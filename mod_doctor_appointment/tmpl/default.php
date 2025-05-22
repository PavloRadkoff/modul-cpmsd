```php
<?php
/**
 * @package     MOD_DOCTOR_APPOINTMENT
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Date\Date;

/**
 * @var  array   $availableSlots  Масив доступних слотів
 * @var  ?array  $result         Результат операції
 * @var  object  $module         Об'єкт модуля
 * @var  \Joomla\Registry\Registry $params Параметри модуля
 * @var  array   $doctors        Список лікарів
 */

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::stylesheet('mod_doctor_appointment/styles.css', ['relative' => true]);

if ($result): ?>
    <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?>">
        <?php echo Text::_($result['message']); ?>
    </div>
<?php endif; ?>

<div class="doctor-appointment-form" id="doctor-appointment-module-<?php echo $module->id; ?>" style="max-width: <?php echo $params->get('form_width', '100%'); ?>;">
    <h3><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_FORM_TITLE'); ?></h3>

    <?php if (empty($doctors)): ?>
        <p><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_NO_DOCTORS_AVAILABLE'); ?></p>
    <?php else: ?>
        <form action="<?php echo Route::_(Uri::getInstance()->toString()); ?>" method="post" class="form-validate" id="appointment-form-<?php echo $module->id; ?>">
            <fieldset>
                <legend><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_SELECT_DOCTOR_LEGEND'); ?></legend>
                <div class="mb-3">
                    <label for="appointment_doctor-<?php echo $module->id; ?>" class="form-label"><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_FIELD_DOCTOR_LABEL'); ?> <span class="required">*</span></label>
                    <select name="appointment_doctor" id="appointment_doctor-<?php echo $module->id; ?>" class="form-select required" required>
                        <option value=""><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_SELECT_DOCTOR'); ?></option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor->id; ?>"><?php echo $doctor->name . ($doctor->specialization ? ' (' . $doctor->specialization . ')' : ''); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_SELECT_SLOT_LEGEND'); ?></legend>
                <?php if (empty($availableSlots)): ?>
                    <p><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_NO_SLOTS_AVAILABLE'); ?></p>
                <?php else: ?>
                    <div class="appointment-slots-container mb-3">
                        <?php foreach ($availableSlots as $dateStr => $slots): ?>
                            <?php
                            $dayDate = new Date($dateStr);
                            $dayLabel = $dayDate->format(Text::_('DATE_FORMAT_LC3'), true);
                            ?>
                            <div class="day-slots mb-2">
                                <strong><?php echo $dayLabel; ?>:</strong>
                                <div class="btn-group" role="group">
                                    <?php foreach ($slots as $slot): ?>
                                        <?php $slotValue = $slot->format('Y-m-d H:i'); ?>
                                        <input type="radio" class="btn-check" name="appointment_slot" id="slot-<?php echo $module->id . '-' . $slot->format('YmdHi'); ?>" value="<?php echo $slotValue; ?>" required>
                                        <label class="btn btn-outline-primary btn-sm" for="slot-<?php echo $module->id . '-' . $slot->format('YmdHi'); ?>">
                                            <?php echo $slot->format(Text::_('TIME_FORMAT_LC4'), true); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div id="slot-error-<?php echo $module->id; ?>" class="invalid-feedback"></div>
                    </div>
                <?php endif; ?>
            </fieldset>

            <fieldset>
                <legend><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_YOUR_DETAILS_LEGEND'); ?></legend>
                <div class="mb-3">
                    <label for="appointment_name-<?php echo $module->id; ?>" class="form-label"><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_FIELD_NAME_LABEL'); ?> <span class="required">*</span></label>
                    <input type="text" name="appointment_name" id="appointment_name-<?php echo $module->id; ?>" class="form-control required" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_phone-<?php echo $module->id; ?>" class="form-label"><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_FIELD_PHONE_LABEL'); ?> <span class="required">*</span></label>
                    <input type="tel" name="appointment_phone" id="appointment_phone-<?php echo $module->id; ?>" class="form-control required" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_email-<?php echo $module->id; ?>" class="form-label"><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_FIELD_EMAIL_LABEL'); ?></label>
                    <input type="email" name="appointment_email" id="appointment_email-<?php echo $module->id; ?>" class="form-control validate-email">
                </div>
            </fieldset>

            <input type="hidden" name="task" value="submit_appointment" />
            <?php echo HTMLHelper::_('form.token'); ?>
            <button type="submit" class="btn btn-primary"><?php echo Text::_('MOD_DOCTOR_APPOINTMENT_SUBMIT_BUTTON'); ?></button>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('appointment-form-<?php echo $module->id; ?>');
                if (form) {
                    form.addEventListener('submit', function(event) {
                        const checkedRadio = form.querySelector('input[name="appointment_slot"]:checked');
                        const errorDiv = document.getElementById('slot-error-<?php echo $module->id; ?>');
                        if (!checkedRadio) {
                            errorDiv.textContent = '<?php echo Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_SLOT_REQUIRED_JS', true); ?>';
                            event.preventDefault();
                        } else {
                            errorDiv.textContent = '';
                        }
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>
```