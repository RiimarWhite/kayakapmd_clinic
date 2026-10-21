<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduleModel;

class DoctorsProfileModel extends Model
{
    protected $table = 'doctors';

    protected $primaryKey = 'docrefno';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doccode',
        'docrefno',
        'doclname',
        'docfname',
        'docmname',
        'suffix',
        'titlename',
        'docname',
        'adrs',
        'emailadd',
        'S2no',
        'PTR',
        'Licno',
        'phicno',
        'tin',
        'phicenable',
        'phicrate',
        'pfrate',
        'lastupdate',
        'proftype',
        'disabletext',
        'cellno',
        'catg',
        'recid',
        'redby',
        'station',
        'groupname',
        'coacode',
        'accountno',
        'tax',
        'issuehospOR',
        'expertise',
        'clinichours',
        'otherinfo',
        'biodata',
        'clinicroom',
        'quevisible',
        'profgroup',
        'autoAddVAT',
        'VAT',
        'vatrate',
        'allowtextresult',
        'allowdocsystem',
        'phicexpiry',
        'licnoexpiry',
        'status',
        'statusreason',
        'vatable',
        'rodrate',
        'phicname',
        'department',
        'docfirst',
        'specializations'
    ];

    public $timestamps = false;

    public function casts() : array 
    {
        return [
            'specializations' => 'array',
            'phicenable' => 'boolean',
            'lastupdate' => 'datetime',
            'issuehospOP' => 'boolean',
            'quevisible' => 'boolean',
            'allowtextresult' => 'boolean',
            'allowdocsystem' => 'boolean',
            'phicexpiry' => 'date',
            'licnoexpiry' => 'date',
            'status' => 'boolean',
            'vatable' => 'boolean'
        ];
    }

    public static function booted()
    {
        static::creating(function ($model) {
            $model->doccode = 'PFMD' . str_pad($model->count() + 1, 3, '0', STR_PAD_LEFT);
        });
    }

    public function doctorrights()
    {
        return $this->hasOne(DoctorModel::class, 'docrefno', 'docrefno');
    }

    /**
     * Detailed Comment: Accessor providing backwards and forwards compatibility between
     * 'consultationfee' (from doctorsrights / DoctorModel) and 'pfrate' (from doctors / DoctorsProfileModel).
     */
    public function getConsultationfeeAttribute(): ?float
    {
        return isset($this->attributes['consultationfee'])
            ? (float)$this->attributes['consultationfee']
            : (isset($this->attributes['pfrate']) ? (float)$this->attributes['pfrate'] : 0.0);
    }

    /**
     * Detailed Comment: Aggregates the doctor's weekly schedules from docschedules (ordered Sunday..Saturday, then by start time),
     * compiles them into a clean, human-readable clinic hours string (e.g. 'Mon, Wed 08:00 AM - 12:00 PM; Fri 01:00 PM - 05:00 PM'),
     * updates the clinichours column on the doctors table, and saves the model.
     *
     * @return string The compiled clinic hours string.
     */
    public function syncClinicHoursFromSchedules(): string
    {
        if (empty($this->docrefno)) {
            return '';
        }

        $dayOrderSql = "CASE day
            WHEN 'Sunday' THEN 1
            WHEN 'Monday' THEN 2
            WHEN 'Tuesday' THEN 3
            WHEN 'Wednesday' THEN 4
            WHEN 'Thursday' THEN 5
            WHEN 'Friday' THEN 6
            WHEN 'Saturday' THEN 7
            ELSE 8
        END ASC, start ASC";

        $schedules = ScheduleModel::where('docrefno', $this->docrefno)
            ->orderByRaw($dayOrderSql)
            ->get();

        if ($schedules->isEmpty()) {
            $this->clinichours = '';
            $this->save();
            return '';
        }

        $dayAbbr = [
            'Sunday' => 'Sun',
            'Monday' => 'Mon',
            'Tuesday' => 'Tue',
            'Wednesday' => 'Wed',
            'Thursday' => 'Thu',
            'Friday' => 'Fri',
            'Saturday' => 'Sat',
        ];

        // Group days having the exact same time window
        $groups = [];
        foreach ($schedules as $sched) {
            $startTime = !empty($sched->start) ? date('h:i A', strtotime($sched->start)) : '';
            $endTime = !empty($sched->end) ? date('h:i A', strtotime($sched->end)) : '';
            $timeSlot = trim($startTime . ' - ' . $endTime);

            $dayLabel = $dayAbbr[$sched->day] ?? $sched->day;
            if (!isset($groups[$timeSlot])) {
                $groups[$timeSlot] = [];
            }
            if (!in_array($dayLabel, $groups[$timeSlot], true)) {
                $groups[$timeSlot][] = $dayLabel;
            }
        }

        $parts = [];
        foreach ($groups as $timeSlot => $days) {
            $daysStr = implode(', ', $days);
            $parts[] = !empty($timeSlot) && $timeSlot !== '-' ? "{$daysStr} {$timeSlot}" : $daysStr;
        }

        $formatted = implode('; ', $parts);
        $this->clinichours = $formatted;
        $this->save();

        return $formatted;
    }
}

