-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Ноя 23 2025 г., 20:22
-- Версия сервера: 8.0.43
-- Версия PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `KitOper`
--

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_schedules`
--

CREATE TABLE `first_course_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `study_day` enum('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота') COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_number` tinyint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `subject_id_2` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id_2` bigint UNSIGNED DEFAULT NULL,
  `room_id` bigint UNSIGNED DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `subgroup` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_schedules`
--

INSERT INTO `first_course_schedules` (`id`, `study_day`, `lesson_number`, `group_id`, `subject_id`, `subject_id_2`, `teacher_id`, `teacher_id_2`, `room_id`, `room_id_2`, `subgroup`, `is_replacement`, `created_at`, `updated_at`) VALUES
(1, 'Понедельник', 1, 12, 12, NULL, 10, NULL, NULL, NULL, NULL, 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(2, 'Понедельник', 2, 12, 24, NULL, 33, NULL, NULL, NULL, '1', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(3, 'Понедельник', 2, 12, 8, NULL, 5, NULL, NULL, NULL, '2', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(4, 'Понедельник', 3, 12, 5, NULL, 27, NULL, NULL, NULL, NULL, 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(5, 'Понедельник', 4, 12, 6, NULL, 33, NULL, NULL, NULL, '1', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(6, 'Понедельник', 4, 12, 6, NULL, 25, NULL, NULL, NULL, '2', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
