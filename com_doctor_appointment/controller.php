```php
<?php
/**
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DoctorAppointmentsController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->getCmd('view', 'appointments');
        $this->input->set('view', $view);
        parent::display($cachable, $urlparams);
        return $this;
    }
}
```