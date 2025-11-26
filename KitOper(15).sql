-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Ноя 26 2025 г., 09:35
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
-- Структура таблицы `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_group`
--

CREATE TABLE `first_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_group`
--

INSERT INTO `first_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `created_at`, `updated_at`) VALUES
(1, 'ТЭ-115', 115, NULL, NULL, NULL),
(2, 'БҚЕ-115', 115, NULL, NULL, NULL),
(3, 'БҚЕ-125', 125, NULL, NULL, NULL),
(4, 'БҚЕ-135', 135, NULL, NULL, NULL),
(5, 'ПО-115', 115, NULL, NULL, NULL),
(6, 'ПО-145', 145, NULL, NULL, NULL),
(7, 'ПО-155', 155, NULL, NULL, NULL),
(8, 'ПО-165', 165, NULL, NULL, NULL),
(9, 'ПО-175', 175, NULL, NULL, NULL),
(10, 'ПО-185', 185, NULL, NULL, NULL),
(11, 'ПО-195', 195, NULL, NULL, NULL),
(12, 'АҚЖ-115', 115, NULL, NULL, NULL),
(13, 'АҚЖ-125', 125, NULL, NULL, NULL),
(14, 'СИБ-135', 135, NULL, NULL, NULL),
(15, 'СИБ-145', 145, NULL, NULL, NULL),
(16, 'М-115', 115, NULL, NULL, NULL),
(17, 'М-125', 125, NULL, NULL, NULL),
(18, 'М-135', 135, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_schedules`
--

CREATE TABLE `first_course_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `week_start` date DEFAULT NULL,
  `study_day` enum('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_number` tinyint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `subject_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `subject_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `subject_id_2` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `teacher_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `teacher_id_2` bigint UNSIGNED DEFAULT NULL,
  `room_id` bigint UNSIGNED DEFAULT NULL,
  `is_absent_1_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_absent_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_den` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `room_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_den` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mode` varchar(12) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS ((case when ((`subject_id_denominator` is null) and (`teacher_id_denominator` is null) and (`room_id_denominator` is null)) then _utf8mb4'single' else _utf8mb4'numerator' end)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_schedules`
--

INSERT INTO `first_course_schedules` (`id`, `week_start`, `study_day`, `lesson_number`, `group_id`, `subject_id`, `subject_id_denominator`, `subject_id_denominator_2`, `subject_id_2`, `teacher_id`, `teacher_id_denominator`, `teacher_id_denominator_2`, `teacher_id_2`, `room_id`, `is_absent_1_num`, `is_replacement_1_num`, `replacement_teacher_id_1_num`, `replacement_comment_1_num`, `is_absent_1_den`, `is_replacement_1_den`, `replacement_teacher_id_1_den`, `replacement_comment_1_den`, `room_id_denominator`, `room_id_denominator_2`, `is_absent_2_den`, `is_replacement_2_den`, `replacement_teacher_id_2_den`, `replacement_comment_2_den`, `room_id_2`, `is_absent_2_num`, `is_replacement_2_num`, `replacement_teacher_id_2_num`, `replacement_comment_2_num`, `subgroup`, `is_replacement`, `created_at`, `updated_at`) VALUES
(1, '2025-11-24', 'Понедельник', 1, 12, 12, NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(4, '2025-11-24', 'Понедельник', 3, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(5, '2025-11-24', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(6, '2025-11-24', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(7, '2025-11-24', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 46, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(8, '2025-11-24', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(9, '2025-11-24', 'Вторник', 2, 12, 1, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(10, '2025-11-24', 'Вторник', 3, 12, 10, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(11, '2025-11-24', 'Среда', 1, 12, 8, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(12, '2025-11-24', 'Среда', 1, 12, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(13, '2025-11-24', 'Среда', 2, 12, 9, 11, NULL, NULL, 24, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(14, '2025-11-24', 'Среда', 3, 12, 15, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(15, '2025-11-24', 'Четверг', 1, 12, 3, 1, NULL, NULL, 32, 7, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(16, '2025-11-24', 'Четверг', 1, 12, 3, 1, NULL, NULL, 34, 7, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(17, '2025-11-24', 'Четверг', 2, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(18, '2025-11-24', 'Четверг', 3, 12, 15, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(19, '2025-11-24', 'Четверг', 4, 12, 11, NULL, NULL, NULL, 45, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(20, '2025-11-24', 'Пятница', 1, 12, 2, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(21, '2025-11-24', 'Пятница', 2, 12, 13, NULL, NULL, NULL, 23, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(22, '2025-11-24', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(23, '2025-11-24', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(24, '2025-11-24', 'Пятница', 4, 12, 26, NULL, NULL, NULL, 24, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, '2025-11-26 09:33:19', '2025-11-26 09:33:19'),
(33, '2025-11-24', 'Понедельник', 2, 12, 14, 8, NULL, NULL, 33, 37, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '1', 0, '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(34, '2025-11-24', 'Понедельник', 2, 12, NULL, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0, 0, NULL, NULL, '2', 0, '2025-11-26 09:33:46', '2025-11-26 09:33:46');

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_subjects`
--

CREATE TABLE `first_course_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `module_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_index` int DEFAULT NULL,
  `subject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kz` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_subjects`
--

INSERT INTO `first_course_subjects` (`id`, `module_title`, `module_index`, `subject_name`, `name_ru`, `name_kz`, `created_at`, `updated_at`) VALUES
(1, 'ООД 1', 1, 'Русский язык', 'Русский язык', 'Орыс тілі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(2, 'ООД 2', 2, 'Русская литература', 'Русская литература', 'Орыс әдебиеті', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(3, 'ООД 3', 3, 'Казахский язык и литература', 'Казахский язык и литература', 'Қазақ тілі мен әдебиеті', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(4, 'ООД 4', 4, 'Иностранный язык', 'Иностранный язык', 'Шет тілі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(5, 'ООД 5', 5, 'Математика', 'Математика', 'Математика', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(6, 'ООД 6', 6, 'Информатика', 'Информатика', 'Информатика', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(7, 'ООД 7', 7, 'История Казахстана', 'История Казахстана', 'Қазақстан тарихы', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(8, 'ООД 8', 8, 'Физическая культура', 'Физическая культура', 'Дене тәрбиесі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(9, 'ООД 9', 9, 'НВТП', 'НВТП', 'НВТП', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(10, 'ООД 10', 10, 'Физика', 'Физика', 'Физика', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(11, 'ООД 11', 11, 'Химия', 'Химия', 'Химия', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(12, 'ООД 12', 12, 'Биология', 'Биология', 'Биология', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(13, 'ООД 13', 13, 'География', 'География', 'География', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(14, 'ООД 14', 14, 'Графика и проектирование', 'Графика и проектирование', 'Графика және жобалау', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(15, 'ООД 15', 15, 'Всемирная история', 'Всемирная история', 'Дүниежүзі тарихы', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(16, 'ООД 16', 16, 'Глобальные компетенции', 'Глобальные компетенции', 'Ғаламдық құзыреттер', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(26, 'ООД 26', 26, 'НВиТП', 'НВиТП', 'НВиТП', '2025-11-23 14:15:39', '2025-11-23 16:03:20');

-- --------------------------------------------------------

--
-- Структура таблицы `form_two_normatives`
--

CREATE TABLE `form_two_normatives` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` smallint UNSIGNED NOT NULL DEFAULT '0',
  `hours_per_class` tinyint UNSIGNED NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `form_two_records`
--

CREATE TABLE `form_two_records` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `class_date` date DEFAULT NULL,
  `lesson_number` tinyint UNSIGNED DEFAULT NULL,
  `day` tinyint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `subgroup` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `total_hours` smallint UNSIGNED DEFAULT '0',
  `hours_per_class` tinyint UNSIGNED DEFAULT '2',
  `status` enum('normal','sick','replacement') DEFAULT 'normal',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `bonus_hours` tinyint UNSIGNED DEFAULT NULL,
  `used_hours` tinyint UNSIGNED DEFAULT '0',
  `absent_reason` varchar(255) DEFAULT NULL,
  `replacement_comment` varchar(255) DEFAULT NULL,
  `mode` enum('single','numerator','denominator') DEFAULT 'single',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `form_two_records`
--

INSERT INTO `form_two_records` (`id`, `group_id`, `month`, `year`, `class_date`, `lesson_number`, `day`, `subject_id`, `teacher_id`, `subgroup`, `total_hours`, `hours_per_class`, `status`, `replacement_teacher_id`, `bonus_hours`, `used_hours`, `absent_reason`, `replacement_comment`, `mode`, `created_at`, `updated_at`) VALUES
(238, 12, 11, 2025, '2025-11-24', 1, 24, 12, 10, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(239, 12, 11, 2025, '2025-11-24', 3, 24, 5, 27, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(240, 12, 11, 2025, '2025-11-24', 4, 24, 6, 25, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(241, 12, 11, 2025, '2025-11-25', 1, 25, 3, 34, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(242, 12, 11, 2025, '2025-11-25', 2, 25, 1, 7, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(243, 12, 11, 2025, '2025-11-25', 3, 25, 10, 1, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(244, 12, 11, 2025, '2025-11-26', 1, 26, 8, 5, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(245, 12, 11, 2025, '2025-11-26', 2, 26, 11, 45, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'denominator', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(246, 12, 11, 2025, '2025-11-26', 3, 26, 15, 41, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(247, 12, 11, 2025, '2025-11-27', 1, 27, 1, 7, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'denominator', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(248, 12, 11, 2025, '2025-11-27', 2, 27, 5, 27, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(249, 12, 11, 2025, '2025-11-27', 3, 27, 15, 41, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(250, 12, 11, 2025, '2025-11-27', 4, 27, 11, 45, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(251, 12, 11, 2025, '2025-11-28', 1, 28, 2, 7, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(252, 12, 11, 2025, '2025-11-28', 2, 28, 13, 23, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(253, 12, 11, 2025, '2025-11-28', 3, 28, 4, 19, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(254, 12, 11, 2025, '2025-11-28', 4, 28, 26, 24, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'single', '2025-11-26 09:33:46', '2025-11-26 09:33:46'),
(255, 12, 11, 2025, '2025-11-24', 2, 24, 8, 5, 1, 0, 2, 'normal', NULL, NULL, 2, NULL, NULL, 'denominator', '2025-11-26 09:33:46', '2025-11-26 09:33:46');

-- --------------------------------------------------------

--
-- Структура таблицы `frist_course_teachers`
--

CREATE TABLE `frist_course_teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `initials` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `frist_course_teachers`
--

INSERT INTO `frist_course_teachers` (`id`, `initials`, `teacher_name`, `created_at`, `updated_at`) VALUES
(1, 'Айнабекова Б.О.', 'Айнабекова Б.О.', NULL, NULL),
(2, 'Алдажуманов Т.К.', 'Алдажуманов Т.К.', NULL, NULL),
(3, 'Алданов Р.А.', 'Алданов Р.А.', NULL, NULL),
(4, 'Альдекенов Т.С.', 'Альдекенов Т.С.', NULL, NULL),
(5, 'Арыкова А.А.', 'Арыкова А.А.', NULL, NULL),
(6, 'Ахмедьянова А.М.', 'Ахмедьянова А.М.', NULL, NULL),
(7, 'Ахменова А.Е.', 'Ахменова А.Е.', NULL, NULL),
(9, 'Аяпберген Н.Е.', 'Аяпберген Н.Е.', NULL, NULL),
(10, 'Баймухамбетов Б.В.', 'Баймухамбетов Б.В.', NULL, NULL),
(11, 'Бондарь В.Н.', 'Бондарь В.Н.', NULL, NULL),
(12, 'Бралина М.Д.', 'Бралина М.Д.', NULL, NULL),
(14, 'Жагапарова Г.С.', 'Жагапарова Г.С.', NULL, NULL),
(16, 'Жамбұл А.Қ.', 'Жамбұл А.Қ.', NULL, NULL),
(17, 'Жотеков А.Ш.', 'Жотеков А.Ш.', NULL, NULL),
(18, 'Иванова И.Н.', 'Иванова И.Н.', NULL, NULL),
(19, 'Измайлова Е.В.', 'Измайлова Е.В.', NULL, NULL),
(20, 'Канагатова М.С.', 'Канагатова М.С.', NULL, NULL),
(21, 'Карпаева Л.Б.', 'Карпаева Л.Б.', NULL, NULL),
(22, 'Косбармаков А.Д.', 'Косбармаков А.Д.', NULL, NULL),
(23, 'Ксембаева Д.М.', 'Ксембаева Д.М.', NULL, NULL),
(24, 'Кульмуратов А.К.', 'Кульмуратов А.К.', NULL, NULL),
(25, 'Курмангазина А.Ж.', 'Курмангазина А.Ж.', NULL, NULL),
(26, 'Курмангалина А.Ж.', 'Курмангалина А.Ж.', NULL, NULL),
(27, 'Мадениятова Г.Д.', 'Мадениятова Г.Д.', NULL, NULL),
(28, 'Молгаждарова М.К.', 'Молгаждарова М.К.', NULL, NULL),
(29, 'Мухамеджанова К.Б.', 'Мухамеджанова К.Б.', NULL, NULL),
(30, 'Мухамедьярова А.И.', 'Мухамедьярова А.И.', NULL, NULL),
(31, 'Мухаметжанова К.Б.', 'Мухаметжанова К.Б.', NULL, NULL),
(32, 'Мынгышева А.А.', 'Мынгышева А.А.', NULL, NULL),
(33, 'Нестеров И.Ю.', 'Нестеров И.Ю.', NULL, NULL),
(34, 'Нурмагамбетова Л.Б.', 'Нурмагамбетова Л.Б.', NULL, NULL),
(35, 'Нурмагамбетова Н.С.', 'Нурмагамбетова Н.С.', NULL, NULL),
(36, 'Нұрпейіс Н.Т.', 'Нұрпейіс Н.Т.', NULL, NULL),
(37, 'Окенов Р.Н.', 'Окенов Р.Н.', NULL, NULL),
(38, 'Пилипенко А.А.', 'Пилипенко А.А.', NULL, NULL),
(39, 'Рахметова М.А.', 'Рахметова М.А.', NULL, NULL),
(40, 'Серёгина Е.А.', 'Серёгина Е.А.', NULL, NULL),
(41, 'Солтанова А.М.', 'Солтанова А.М.', NULL, NULL),
(42, 'Султангазинова Д.С.', 'Султангазинова Д.С.', NULL, NULL),
(43, 'Табулдинов Б.К.', 'Табулдинов Б.К.', NULL, NULL),
(44, 'Тауымова А.Е.', 'Тауымова А.Е.', NULL, NULL),
(45, 'Трубецкая Т.Н.', 'Трубецкая Т.Н.', NULL, NULL),
(46, 'вакансия', 'вакансия', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

CREATE TABLE `groups` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialty_id` bigint UNSIGNED NOT NULL,
  `year` tinyint UNSIGNED NOT NULL,
  `group_number` tinyint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_23_175206_create_schedule_lessons_table', 1),
(5, '2025_11_24_120000_add_denominator_fields_to_first_course_schedules', 2),
(6, '2026_02_01_120000_add_room_slot_indexes_to_first_course_schedules', 3),
(7, '2026_02_01_131000_add_denominator_subgroup2_columns_to_first_course_schedules', 3),
(8, '2026_02_15_120000_create_schedule_replacements_table', 4),
(9, '2026_02_20_000000_add_replacements_and_week_start_to_first_course_schedules', 4),
(10, '2026_02_20_000100_add_subgroup_and_date_to_form_two_records', 4),
(11, '2026_02_25_000000_create_form_two_normatives_table', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `schedule_lessons`
--

CREATE TABLE `schedule_lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `day_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pair_number` tinyint UNSIGNED NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replaced` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `schedule_replacements`
--

CREATE TABLE `schedule_replacements` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subgroup` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `study_day` enum('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота') NOT NULL,
  `lesson_number` tinyint UNSIGNED NOT NULL,
  `week_mode` enum('single','numerator','denominator') NOT NULL DEFAULT 'single',
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `absent_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('j66RBg7m2Q5btrbZPrMlSAxpSAgFF6oit9FTvcPz', NULL, '172.32.0.1', 'Mozilla/5.0 (X11; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidnZHVlJMcEZyaW1xU3B4akNPTklNbjNjUllwVGp1NUV4eDlNSktEbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Nzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9maXJzdC1jb3Vyc2Uvc2NoZWR1bGU/d2Vla19tb2RlPWRlbiZ3ZWVrX3N0YXJ0PTIwMjUtMTEtMjQiO3M6NToicm91dGUiO3M6MjA6ImZpcnN0LnNjaGVkdWxlLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1764149690);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Индексы таблицы `first_course_group`
--
ALTER TABLE `first_course_group`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_course_schedules_room_mode_idx` (`room_id`,`study_day`,`lesson_number`,`mode`),
  ADD KEY `first_course_schedules_room_den_idx` (`room_id_denominator`,`study_day`,`lesson_number`),
  ADD KEY `first_course_schedules_group_week_idx` (`group_id`,`week_start`);

--
-- Индексы таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_normative` (`group_id`,`subject_id`,`teacher_id`,`month`,`year`),
  ADD UNIQUE KEY `form2_normative_unique` (`group_id`,`subject_id`,`teacher_id`,`month`,`year`),
  ADD KEY `fk_norm_subject` (`subject_id`),
  ADD KEY `fk_norm_teacher` (`teacher_id`);

--
-- Индексы таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_record_full` (`group_id`,`year`,`month`,`day`,`subject_id`,`mode`,`subgroup`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_replacement_teacher` (`replacement_teacher_id`),
  ADD KEY `form2_group_date_lesson_mode_idx` (`group_id`,`class_date`,`lesson_number`,`subgroup`,`mode`);

--
-- Индексы таблицы `frist_course_teachers`
--
ALTER TABLE `frist_course_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Индексы таблицы `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Индексы таблицы `schedule_lessons`
--
ALTER TABLE `schedule_lessons`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `schedule_replacements`
--
ALTER TABLE `schedule_replacements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_group_day_lesson` (`group_id`,`study_day`,`lesson_number`,`week_mode`,`subgroup`),
  ADD KEY `fk_repl_subject` (`subject_id`),
  ADD KEY `fk_repl_absent` (`absent_teacher_id`),
  ADD KEY `fk_repl_replacement` (`replacement_teacher_id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `first_course_group`
--
ALTER TABLE `first_course_group`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `schedule_lessons`
--
ALTER TABLE `schedule_lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `schedule_replacements`
--
ALTER TABLE `schedule_replacements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  ADD CONSTRAINT `fk_norm_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_norm_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_norm_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  ADD CONSTRAINT `fk_form2_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_form2_repl_teacher` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_form2_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_form2_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `schedule_replacements`
--
ALTER TABLE `schedule_replacements`
  ADD CONSTRAINT `fk_repl_absent` FOREIGN KEY (`absent_teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_repl_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_repl_replacement` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_repl_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
