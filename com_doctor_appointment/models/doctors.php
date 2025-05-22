<?php
/** 
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class DoctorAppointmentsModelDoctors extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'name', 'specialization'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('id, name, specialization')
            ->from($db->quoteName('#__doctors'))
            ->order($db->quoteName('name') . ' ASC');
        return $query;
    }
}