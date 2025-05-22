```sql
CREATE TABLE IF NOT EXISTS `#__doctor_appointments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` INT(11) UNSIGNED NOT NULL,
  `patient_name` VARCHAR(255) NOT NULL DEFAULT '',
  `patient_phone` VARCHAR(50) NOT NULL DEFAULT '',
  `patient_email` VARCHAR(255) DEFAULT NULL,
  `appointment_datetime` DATETIME NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `callcenter_status_id` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appointment_datetime` (`appointment_datetime`),
  KEY `idx_status` (`status`),
  KEY `idx_doctor_id` (`doctor_id`),
  KEY `idx_callcenter_status_id` (`callcenter_status_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `#__doctors` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`callcenter_status_id`) REFERENCES `#__callcenter_statuses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__doctors` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `specialization` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__callcenter_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка початкових даних для лікарів (9 лікарів)
INSERT INTO `#__doctors` (`name`, `specialization`) VALUES
('Іванов Іван Іванович', 'Сімейний лікар'),
('Петрова Анна Сергіївна', 'Терапевт'),
('Сидоренко Олексій Петрович', 'Педіатр'),
('Коваленко Марія Іванівна', 'Сімейний лікар'),
('Мельник Володимир Олегович', 'Терапевт'),
('Шевченко Тетяна Миколаївна', 'Педіатр'),
('Бондаренко Дмитро Васильович', 'Сімейний лікар'),
('Лисенко Олена Павлівна', 'Терапевт'),
('Кравець Юрій Олександрович', 'Педіатр');

-- Вставка початкових статусів відпрацювання
INSERT INTO `#__callcenter_statuses` (`status_name`) VALUES
('Підтверджено'),
('Скасовано'),
('Не відповів'),
('Перенесено');
```