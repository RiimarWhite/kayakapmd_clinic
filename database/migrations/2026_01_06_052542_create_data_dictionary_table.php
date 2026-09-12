<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dd_menshist', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pMenarchePeriod')->nullable();
            $table->date('pLastMensPeriod')->nullable();
            $table->unsignedInteger('pPeriodDuration')->nullable();
            $table->unsignedInteger('pMensInterval')->nullable();
            $table->unsignedInteger('pPadsPerDay')->nullable();
            $table->unsignedInteger('pOnsetSexIc')->nullable();
            $table->string('pBirthCtrlMethod', 21)->nullable();
            $table->enum('pIsMenopause', ['Y', 'N'])->nullable();
            $table->unsignedInteger('pMenopauseAge')->nullable();
            $table->enum('pIsApplicable', ['Y', 'N']);
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dd_preghist', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('pPregCnt')->nullable();
            $table->unsignedInteger('pDeliveryCnt')->nullable();
            $table->enum('pDeliveryTyp', ['N', 'O', 'B', 'X'])
                ->nullable()
                ->comment('N = Normal (NSD), O = Operative (CSD), B = Both (NSD & CSD), X = Not Applicable');
            $table->unsignedInteger('pFullTermCnt')->nullable();
            $table->unsignedInteger('pPrematureCnt')->nullable();
            $table->unsignedInteger('pAbortionCnt')->nullable();
            $table->unsignedInteger('pLivChildrenCnt')->nullable();
            $table->enum('pWPregIndhyp', ['Y', 'N'])->nullable();
            $table->enum('pWFamPlan', ['Y', 'N']);
            $table->enum('pIsApplicable', ['Y', 'N']);
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();
            
            $table->timestamps();
        });

        Schema::create('dd_pepert', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('pSystolic');
            $table->unsignedInteger('pDiastolic');
            $table->unsignedInteger('pHr');
            $table->unsignedInteger('pRr');
            $table->unsignedInteger('pTemp')
                ->comment('Temperature in Celsius');
            $table->unsignedInteger('pHeight')
                ->comment('Height of Patient in cm ');
            $table->unsignedInteger('pWeight')
                ->comment('Weight of Patient in kg');
            $table->unsignedInteger('pBMI');
            $table->string('pZScore', 10)->nullable();
            $table->string('pLeftVision', 12)->nullable();
            $table->string('pRightVision', 12)->nullable();
            $table->unsignedInteger('pLength')
                ->nullable()
                ->comment('Length of Patient in cm - for Pediatric
                Patient only age 0-24 Months');
            $table->unsignedInteger('pHeadCirc')->nullable();
            $table->unsignedInteger('pSkinfoldThickness')->nullable();
            $table->unsignedInteger('pWaist')->nullable();
            $table->unsignedInteger('pHip')->nullable();
            $table->unsignedInteger('pLimbs')->nullable();
            $table->unsignedInteger('pMidUpperArmCirc')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_bloodtype', function (Blueprint $table) {
            $table->id();

            $table->enum('pBloodType', ['A+', 'B+', 'AB+', 'O+' ,'A-', 'B-' , 'AB-', 'O-'])
                ->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_pegensurvey', function (Blueprint $table) {
            $table->id();

            $table->enum('pGenSurveyId', [1, 2])
                ->nullable()
                ->comment('1 - Awake and Alert, 2 - Altered Sensorium');
            $table->text('pGenSurveyRem')->nullable();
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_pemisc', function (Blueprint $table) {
            $table->id();

            $table->string('pSkinId', 3)
                ->nullable()
                ->comment('lib_skin_extremities');
            $table->string('pHeentId', 3)
                ->nullable()
                ->comment('lib_heent');
            $table->string('pChestId', 3)
                ->nullable()
                ->comment('lib_chest');
            $table->string('pHeartId', 3)
                ->nullable()
                ->comment('lib_heart');
            $table->string('pAbdomenId', 3)
                ->nullable()
                ->comment('lib_abdomen');
            $table->string('pNeuroId', 3)
                ->nullable()
                ->comment('lib_neuro');
            $table->string('pRectalId', 3)
                ->nullable()
                ->comment('lib_digital_rectal');
            $table->string('pGuId', 3)
                ->nullable()
                ->comment('lib_genitourinary');
            
            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_pespecific', function (Blueprint $table) {
            $table->id();
            
            $table->text('pSkinRem')->nullable();
            $table->text('pHeentRem')->nullable();
            $table->text('pChestRem')->nullable();
            $table->text('pHeartRem')->nullable();
            $table->text('pAbdomenRem')->nullable();
            $table->text('pNeuroRem')->nullable();
            $table->text('pRectalRem')->nullable();
            $table->text('pGuRem')->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_ncdqans', function (Blueprint $table) {
            $table->id();
            
            $table->enum('pQid1_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid2_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid3_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid4_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid5_Yn', ['Y', 'N', 'X'])
                ->nullable()
                ->comment('lib_ncdq, X means Dont know');
            $table->enum('pQid6_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid7_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid8_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid9_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid10_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid11_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid12_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid13_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid14_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid15_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid16_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid17_Yn', ['A', 'B', 'C', 'D', 'E'])
                ->nullable()
                ->comment(
                    'lib_ncdq | A: <10% | B: 10–20% | C: 20–30% | D: 30–40% | E: >=40%'
                );
            $table->enum('pQid18_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid19_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->string('pQid19_Fbsmg', 50)
                ->nullable()
                ->comment('Answer for QID19: FBS/RBS in mg/dL');
            $table->string('pQid19_Fbsmmol', 50)
                ->nullable()
                ->comment('Answer for QID19: FBS/RBS in mmol/L');
            $table->date('pQid19_Fbsdate')->nullable();
            $table->enum('pQid20_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->string('pQid20_Choleval', 3)
                ->nullable()
                ->comment('lib_ncdq, Answer for QID20: Total Cholesterol value in mg/dL');
            $table->date('pQid20_Choledate')
                ->nullable()
                ->comment('Answer for QID20: Date when is the FBS/RBS Value was taken');
            $table->enum('pQid21_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->string('pQid21_Ketonval', 3)
                ->nullable()
                ->comment('lib_ncdq, Answer for QID21: Urine Ketones Value');
            $table->date('pQid21_Ketondate')
                ->nullable()
                ->comment('Answer for QID21: Date when is the Urine Ketones Value was taken');
            $table->enum('pQid22_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->string('pQid22_Proteinval', 3)
                ->nullable()
                ->comment('lib_ncdq, Answer for QID22: Urine Protein Value in mg/dL');
            $table->date('pQid22_Proteindate')
                ->nullable()
                ->comment('Answer for QID22: Date when the Urine Protein Value was taken');
            $table->enum('pQid23_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');
            $table->enum('pQid24_Yn', ['Y', 'N'])
                ->nullable()
                ->comment('lib_ncdq');

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_soap_consultation', function (Blueprint $table) {

            $table->string('pHciTransNo', 21)
                ->primary()
                ->comment('
                    S+ACCRE_NO+YYY
                    Y+MM+5 digits
                    series number =
                    S+XXXXXXXXX+YY
                    YY+MM+99999
                ');
            $table->string('pHciCaseNo', 21)
                ->comment('FK -> dd_enlistment.pHciCaseNo');

            $table->foreign('pHciCaseNo')
                ->references('pHciCaseNo')
                ->on('dd_enlistment')
                ->restrictOnDelete();

            $table->date('pSoapDate')->comment('YYYY-MM-DD');
            $table->string('pPatientPin', 12);
            $table->enum('pPatientType', ['MM', 'DD'])
                ->comment('MM - member, DD - dependent');
            $table->string('pMemPin', 12);
            $table->string('pEffYear', 4)->comment('Effectivity Year');
            $table->string('pATC', 10)->comment('Authorization Transaction Code
                                                Note: Use ‘WALKEDIN’ as value if
                                                pWalkedIn is ‘Y’');
            $table->enum('pIsWalkedIn', ['Y', 'N'])->comment('Is Patient Walked In');
            $table->string('pCoPay', 15)->comment('Patient Co-Payment Amount ');
            $table->date('pTransDate')->comment('YYYY-MM-DD');

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_subjective', function (Blueprint $table) {
            $table->id();

            $table->text('pIllnessHistory')->comment('History of Patient Illnesses');
            $table->text('pSignsSymptoms')->comment('Pertinent Signs and Symptoms on Admission ID');
            $table->text('pOtherComplaint')
                ->nullable()
                ->comment('Other Complaint
                            Note: Required if X is included in
                            pSignsSymptoms');
            $table->text('pPainSite')
                ->nullable()
                ->comment('Site of Pain if Pain Element in
                        Pertinent Signs and Symptoms on
                        Admission is checked
                        Note: Required if 38 is included in
                        pSignsSymptoms');

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');
            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_lipidprofile', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for Lipid Profile ');
            $table->string('pLdl', 50)->nullable()->comment('Value for LDL (mg/dL)');
            $table->string('pHdl', 50)->nullable()->comment('Value for HDL (mg/dL)');
            $table->string('pTotal', 50)->nullable()->comment('Total Value of Cholesterol (mg/dL)');
            $table->string('pCholesterol', 50)->nullable()->comment('Total Value of Cholesterol (mg/dL)');
            $table->string('pTriglycerides', 50)->nullable()->comment('Total Value of Triglycerides (mg/dL)');
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_fbs', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for FBS ');
            $table->string('pGlucoseMg', 50)->nullable()->comment('Value for Glucose in md/Dl');
            $table->string('pGlucoseMmol', 50)->nullable()->comment('Value for Glucose in mmol/L');
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_rbs', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for RBS ');
            $table->string('pGlucoseMg', 50)->nullable()->comment('Value for Glucose in md/Dl');
            $table->string('pGlucoseMmol', 50)->nullable()->comment('Value for Glucose in mmol/L');
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_ecg', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for ECG ');

            $table->enum('pFindings', [1, 2])
                ->nullable()
                ->comment('1 - Awake and Alert, 2 - Altered Sensorium');

            $table->text('pRemarks')->nullable();
            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_fecalysis', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for FECALYSIS ');

            $table->enum('pColor', [1, 2, 3, 4, 5, 6])
                ->nullable()
                ->comment('1 - Brown;
                        2 - Black;
                        3 - Red;
                        4 - White/Grey;
                        5 - Yellow;
                        6 - Green;
                        ');

            $table->enum('pConsistency', [1, 2, 3, 4, 5, 6])
                ->nullable()
                ->comment('1 - Soft;
                        2 - Well-Formed;
                        3 - Semi-Formed;
                        4 - Watery;
                        5 - Mucoid;
                        6 - Hard
                        ');

            $table->string('pRbc', 50)->nullable()->comment('Value for RBC (/hpf)');
            $table->string('pWbc', 50)->nullable()->comment('Value for WBC(/hpf)');
            $table->string('pOva', 50)->nullable()->comment('Value for RBC (=/-)');
            $table->string('pParasite', 50)->nullable()->comment('Value for Parasite (=/-)');

            $table->enum('pBlood', ['P', 'A'])
                ->nullable()
                ->comment('P - Present;
                            A - Absent ');
            $table->string('pPusCells', 50)->nullable()->comment('Value for PUS Cells');

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_papsmear', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for PAPSMEAR ');
            $table->text('pFindings')->nullable();
            $table->text('pImpression')->nullable();

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_ogtt', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for OGTT ');
            
            $table->string('pExamFastingMg', 50)->nullable()->comment('Result in Fasting Examination (mg/dL)');
            $table->string('pExamFastingMmol', 50)->nullable()->comment('Result in Fasting Examination (mmol/L)');
            $table->string('pExamOgttOneHrMg', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mg/dL)');
            $table->string('pExamOgttOneHrMmol', 50)->nullable()->comment('Result in OGTT 1 Hour Examination (mmol/L)');
            $table->string('pExamOgttTwoHrMg', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mg/dL)');
            $table->string('pExamOgttTwoHrMmol', 50)->nullable()->comment('Result in OGTT 2 Hours Examination (mmol/L)');

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });
        
        Schema::create('dd_fobt', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for FOBT ');
            
            $table->enum('pFindings', ['P', 'N'])
                ->nullable()
                ->comment('P - Positive;
                            N - Negative ');

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_creatine', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for creatine ');
            
            $table->text('pFindings')->nullable();

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_ppdtest', function (Blueprint $table) {
            $table->id();
            
            $table->text('pReferralFacility')
                ->nullable()
                ->comment('Name of Referral Facility, if referred');
            $table->date('pLabDate')->nullable()->comment('YYYY-MM-DD, Date of Laboratory for creatine ');
            
            $table->enum('pFindings', ['P', 'N'])
                ->nullable()
                ->comment('P - Positive;
                            N - Negative ');

            $table->date('pDateAdded')->nullable()->comment('YYYY-MM-DD, Date the Record was Added ');
            $table->enum('pStatus', ['D', 'N', 'X', 'W'])
                ->nullable()
                ->comment('“D” - Done
                            “N” - Not yet done
                            “X” - Deferred
                            “W” - Waived ');
            $table->decimal('pDiagnosticLabFee', 10, 2)->nullable();

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });

        Schema::create('dd_document', function (Blueprint $table) {
            $table->id();
            
            $table->string('pHciCaseNo', 21)
                ->comment('FK -> dd_enlistment.pHciCaseNo');
            $table->foreign('pHciCaseNo')
                ->references('pHciCaseNo')
                ->on('dd_enlistment')
                ->restrictOnDelete();

            $table->string('pHciTransNo', 21)
                ->comment('FK -> dd_soap_consultation.pHciTransNo');
            $table->foreign('pHciTransNo')
                ->references('pHciTransNo')
                ->on('dd_soap_consultation')
                ->restrictOnDelete();

            $table->string('pPatientPin', 12)->nullable()->comment('Refer to Members PIN if patient type is MM; Refer to Dependents PIN if type is DD ');

            $table->enum('pPatientType', ['MM', 'DD'])
                ->nullable()
                ->comment('MM - Member, DD - Dependent');
            
            $table->string('pMemPin', 12)->nullable()->comment('Philhealth Identification Number (PIN) of Primary Member');

            $table->enum('pDocumentType', ['EKAS', 'EPRESS', 'OTH'])
                ->nullable()
                ->comment('“EKAS” - electronic KonSulTa
                        Slip
                        “EPRESS” - electronic
                        Prescription Slip
                        “OTH” - Others');
            
            $table->text('pDocumentUrl')
                ->nullable()
                ->comment('URL of document attachment for download');

            $table->date('pTransDate')->nullable()->comment('YYYY-MM-DD | Date when the record inserted');

            $table->enum('pReportStatus', ['V', 'U', 'F'])
                ->nullable()
                ->default('U')
                ->comment('V - Validated, U - Unvalidated, F - Failed');

            $table->text('pDeficiencyRemarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dd_menshist');
        Schema::dropIfExists('dd_preghist');
        Schema::dropIfExists('dd_pepert');
        Schema::dropIfExists('dd_bloodtype');
        Schema::dropIfExists('dd_pegensurvey');
        Schema::dropIfExists('dd_pemisc');
        Schema::dropIfExists('dd_pespecific');
        Schema::dropIfExists('dd_ncdqans');
        Schema::dropIfExists('dd_soap_consultation');
        Schema::dropIfExists('dd_subjective');
        Schema::dropIfExists('dd_lipidprofile');
        Schema::dropIfExists('dd_fbs');
        Schema::dropIfExists('dd_rbs');
        Schema::dropIfExists('dd_ecg');
        Schema::dropIfExists('dd_fecalysis');
        Schema::dropIfExists('dd_papsmear');
        Schema::dropIfExists('dd_ogtt');
        Schema::dropIfExists('dd_fobt');
        Schema::dropIfExists('dd_creatine');
        Schema::dropIfExists('dd_ppdtest');
        Schema::dropIfExists('dd_document');
        
    }
};
