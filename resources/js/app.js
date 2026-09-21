import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import "/node_modules/select2/dist/css/select2.css";


// jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'jquery-ui-dist/jquery-ui';
import 'datatables.net-bs5';
import 'datatables.net-select-bs5';

// Bootstrap JS
import { Modal, Dropdown, Tooltip, Popover } from 'bootstrap';
window.bootstrap = { Modal, Dropdown, Tooltip, Popover };

// SweetAlert
import Swal from 'sweetalert2';
window.Swal = Swal;

// Select2
// Detailed Comment: Import select2 UMD bundle which self-initializes $.fn.select2 on the global jQuery instance without calling select2() directly
import 'select2';


// Custom JS
import './components.js';
import './logout.js';
import './patient.js';
import './secretary-management.js';
