-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Янв 14 2026 г., 06:12
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
  `replaces_schedule_id` bigint UNSIGNED DEFAULT NULL,
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
  `replacement_comment_1_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_subject_replacement_1_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_subject_id_1_num` bigint UNSIGNED DEFAULT NULL,
  `room_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `is_absent_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_subject_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_subject_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `room_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_subject_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_subject_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_subject_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_subject_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mode` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS ((case when ((`subject_id_denominator` is null) and (`teacher_id_denominator` is null) and (`room_id_denominator` is null)) then _utf8mb4'single' else _utf8mb4'numerator' end)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_schedules`
--

INSERT INTO `first_course_schedules` (`id`, `replaces_schedule_id`, `week_start`, `study_day`, `lesson_number`, `group_id`, `subject_id`, `subject_id_denominator`, `subject_id_denominator_2`, `subject_id_2`, `teacher_id`, `teacher_id_denominator`, `teacher_id_denominator_2`, `teacher_id_2`, `room_id`, `is_absent_1_num`, `is_replacement_1_num`, `replacement_teacher_id_1_num`, `replacement_comment_1_num`, `is_subject_replacement_1_num`, `replacement_subject_id_1_num`, `room_id_denominator`, `is_absent_1_den`, `is_replacement_1_den`, `replacement_teacher_id_1_den`, `replacement_comment_1_den`, `is_subject_replacement_1_den`, `replacement_subject_id_1_den`, `room_id_denominator_2`, `is_absent_2_den`, `is_replacement_2_den`, `replacement_teacher_id_2_den`, `replacement_comment_2_den`, `is_subject_replacement_2_den`, `replacement_subject_id_2_den`, `room_id_2`, `is_absent_2_num`, `is_replacement_2_num`, `replacement_teacher_id_2_num`, `replacement_comment_2_num`, `is_subject_replacement_2_num`, `replacement_subject_id_2_num`, `subgroup`, `is_replacement`, `created_at`, `updated_at`) VALUES
(1, NULL, '2025-09-01', 'Понедельник', 1, 12, 12, NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(2, NULL, '2025-09-01', 'Понедельник', 2, 12, 14, 8, NULL, NULL, 33, 37, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(3, NULL, '2025-09-01', 'Понедельник', 2, 12, NULL, NULL, 8, NULL, NULL, NULL, 5, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(4, NULL, '2025-09-01', 'Понедельник', 3, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(5, NULL, '2025-09-01', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(6, NULL, '2025-09-01', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(7, NULL, '2025-09-01', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 46, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(8, NULL, '2025-09-01', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(9, NULL, '2025-09-01', 'Вторник', 2, 12, 1, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(10, NULL, '2025-09-01', 'Вторник', 3, 12, 10, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(11, NULL, '2025-09-01', 'Среда', 1, 12, 8, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(12, NULL, '2025-09-01', 'Среда', 1, 12, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(13, NULL, '2025-09-01', 'Среда', 2, 12, 9, 11, NULL, NULL, 24, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(14, NULL, '2025-09-01', 'Среда', 3, 12, 7, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(15, NULL, '2025-09-01', 'Четверг', 1, 12, 3, 1, NULL, NULL, 32, 7, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(16, NULL, '2025-09-01', 'Четверг', 1, 12, 3, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(17, NULL, '2025-09-01', 'Четверг', 2, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(18, NULL, '2025-09-01', 'Четверг', 3, 12, 15, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(19, NULL, '2025-09-01', 'Четверг', 4, 12, 11, NULL, NULL, NULL, 45, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(20, NULL, '2025-09-01', 'Пятница', 1, 12, 2, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(21, NULL, '2025-09-01', 'Пятница', 2, 12, 13, NULL, NULL, NULL, 23, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(22, NULL, '2025-09-01', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(23, NULL, '2025-09-01', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(24, NULL, '2025-09-01', 'Пятница', 4, 12, 26, NULL, NULL, NULL, 24, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(25, NULL, '2025-09-08', 'Понедельник', 1, 12, 12, NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(26, NULL, '2025-09-08', 'Понедельник', 2, 12, 14, 8, NULL, NULL, 33, 37, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(27, NULL, '2025-09-08', 'Понедельник', 2, 12, NULL, NULL, 8, NULL, NULL, NULL, 5, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(28, NULL, '2025-09-08', 'Понедельник', 3, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(29, NULL, '2025-09-08', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(30, NULL, '2025-09-08', 'Понедельник', 4, 12, 6, NULL, NULL, NULL, 25, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(31, NULL, '2025-09-08', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 46, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(32, NULL, '2025-09-08', 'Вторник', 1, 12, 3, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(33, NULL, '2025-09-08', 'Вторник', 2, 12, 1, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(34, NULL, '2025-09-08', 'Вторник', 3, 12, 10, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(35, NULL, '2025-09-08', 'Среда', 1, 12, 8, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(36, NULL, '2025-09-08', 'Среда', 1, 12, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(37, NULL, '2025-09-08', 'Среда', 2, 12, 9, 11, NULL, NULL, 24, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(38, NULL, '2025-09-08', 'Среда', 3, 12, 7, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(39, NULL, '2025-09-08', 'Четверг', 1, 12, 3, 1, NULL, NULL, 32, 7, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(40, NULL, '2025-09-08', 'Четверг', 1, 12, 3, NULL, NULL, NULL, 34, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(41, NULL, '2025-09-08', 'Четверг', 2, 12, 5, NULL, NULL, NULL, 27, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(42, NULL, '2025-09-08', 'Четверг', 3, 12, 15, NULL, NULL, NULL, 41, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(43, NULL, '2025-09-08', 'Четверг', 4, 12, 11, NULL, NULL, NULL, 45, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(44, NULL, '2025-09-08', 'Пятница', 1, 12, 2, NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(45, NULL, '2025-09-08', 'Пятница', 2, 12, 13, NULL, NULL, NULL, 23, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(46, NULL, '2025-09-08', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(47, NULL, '2025-09-08', 'Пятница', 3, 12, 4, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(48, NULL, '2025-09-08', 'Пятница', 4, 12, 26, NULL, NULL, NULL, 24, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-14 05:59:52', '2026-01-14 05:59:52');

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
  `status` enum('normal','sick','replacement','replaced','replacement_subject') DEFAULT 'normal',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id` bigint UNSIGNED DEFAULT NULL,
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

INSERT INTO `form_two_records` (`id`, `group_id`, `month`, `year`, `class_date`, `lesson_number`, `day`, `subject_id`, `teacher_id`, `subgroup`, `total_hours`, `hours_per_class`, `status`, `replacement_teacher_id`, `replacement_subject_id`, `bonus_hours`, `used_hours`, `absent_reason`, `replacement_comment`, `mode`, `created_at`, `updated_at`) VALUES
(59, 12, 9, 2025, '2025-09-01', 1, 1, 12, 10, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(60, 12, 9, 2025, '2025-09-01', 2, 1, 14, 33, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(61, 12, 9, 2025, '2025-09-01', 3, 1, 5, 27, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(62, 12, 9, 2025, '2025-09-01', 4, 1, 6, 33, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(63, 12, 9, 2025, '2025-09-01', 4, 1, 6, 25, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(64, 12, 9, 2025, '2025-09-02', 1, 2, 3, 46, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(65, 12, 9, 2025, '2025-09-02', 1, 2, 3, 34, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(66, 12, 9, 2025, '2025-09-02', 2, 2, 1, 7, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(67, 12, 9, 2025, '2025-09-02', 3, 2, 10, 1, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(68, 12, 9, 2025, '2025-09-03', 1, 3, 8, 37, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(69, 12, 9, 2025, '2025-09-03', 1, 3, 8, 5, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(70, 12, 9, 2025, '2025-09-03', 2, 3, 9, 24, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(71, 12, 9, 2025, '2025-09-03', 3, 3, 7, 41, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(72, 12, 9, 2025, '2025-09-04', 1, 4, 3, 32, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(73, 12, 9, 2025, '2025-09-04', 1, 4, 3, 34, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(74, 12, 9, 2025, '2025-09-04', 2, 4, 5, 27, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(75, 12, 9, 2025, '2025-09-04', 3, 4, 15, 41, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(76, 12, 9, 2025, '2025-09-04', 4, 4, 11, 45, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(77, 12, 9, 2025, '2025-09-05', 1, 5, 2, 7, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(78, 12, 9, 2025, '2025-09-05', 2, 5, 13, 23, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(79, 12, 9, 2025, '2025-09-05', 3, 5, 4, 12, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(80, 12, 9, 2025, '2025-09-05', 3, 5, 4, 19, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(81, 12, 9, 2025, '2025-09-05', 4, 5, 26, 24, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(82, 12, 9, 2025, '2025-09-08', 1, 8, 12, 10, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(83, 12, 9, 2025, '2025-09-08', 2, 8, 8, 37, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(84, 12, 9, 2025, '2025-09-08', 2, 8, 8, 5, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(85, 12, 9, 2025, '2025-09-08', 3, 8, 5, 27, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(86, 12, 9, 2025, '2025-09-08', 4, 8, 6, 33, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(87, 12, 9, 2025, '2025-09-08', 4, 8, 6, 25, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(88, 12, 9, 2025, '2025-09-09', 1, 9, 3, 46, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(89, 12, 9, 2025, '2025-09-09', 1, 9, 3, 34, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(90, 12, 9, 2025, '2025-09-09', 2, 9, 1, 7, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(91, 12, 9, 2025, '2025-09-09', 3, 9, 10, 1, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(92, 12, 9, 2025, '2025-09-10', 1, 10, 8, 37, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(93, 12, 9, 2025, '2025-09-10', 1, 10, 8, 5, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(94, 12, 9, 2025, '2025-09-10', 2, 10, 11, 45, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(95, 12, 9, 2025, '2025-09-10', 3, 10, 7, 41, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(96, 12, 9, 2025, '2025-09-11', 1, 11, 1, 7, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(97, 12, 9, 2025, '2025-09-11', 1, 11, 3, 34, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(98, 12, 9, 2025, '2025-09-11', 2, 11, 5, 27, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(99, 12, 9, 2025, '2025-09-11', 3, 11, 15, 41, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(100, 12, 9, 2025, '2025-09-11', 4, 11, 11, 45, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(101, 12, 9, 2025, '2025-09-12', 1, 12, 2, 7, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(102, 12, 9, 2025, '2025-09-12', 2, 12, 13, 23, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(103, 12, 9, 2025, '2025-09-12', 3, 12, 4, 12, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(104, 12, 9, 2025, '2025-09-12', 3, 12, 4, 19, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52'),
(105, 12, 9, 2025, '2025-09-12', 4, 12, 26, 24, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-14 05:59:52', '2026-01-14 05:59:52');

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_course_group`
--

CREATE TABLE `fourth_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `fourth_course_group`
--

INSERT INTO `fourth_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `created_at`, `updated_at`) VALUES
(1, 'ТЭ-422', 422, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_course_schedules`
--

CREATE TABLE `fourth_course_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `replaces_schedule_id` bigint UNSIGNED DEFAULT NULL,
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
  `replacement_subject_id_1_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `is_absent_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_course_subjects`
--

CREATE TABLE `fourth_course_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `module_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_index` int DEFAULT NULL,
  `subject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kz` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_course_teachers`
--

CREATE TABLE `fourth_course_teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_form_two_normatives`
--

CREATE TABLE `fourth_form_two_normatives` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_form_two_records`
--

CREATE TABLE `fourth_form_two_records` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `day` tinyint UNSIGNED DEFAULT NULL,
  `class_date` date DEFAULT NULL,
  `lesson_number` tinyint UNSIGNED DEFAULT NULL,
  `subgroup` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `status` enum('normal','sick','replacement','replaced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id` bigint UNSIGNED DEFAULT NULL,
  `bonus_hours` int DEFAULT NULL,
  `used_hours` int NOT NULL DEFAULT '0',
  `absent_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replacement_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(11, '2026_02_25_000000_create_form_two_normatives_table', 5),
(12, '2026_11_27_000200_add_replaced_status_to_form_two_records', 6),
(13, '2026_11_28_000000_add_replacement_subject_status_to_form_two_records', 7),
(14, '2025_11_27_065525_add_replacement_subject_status_to_form_two_records', 8),
(15, '2025_11_27_080013_refactor_replacements_logic', 9),
(16, '2026_11_28_000000_add_subject_replacement_columns_to_first_course_schedules', 10),
(17, '2026_11_30_000300_add_replacement_subjects', 11),
(18, '2027_01_01_000000_add_multi_course_tables', 12),
(19, '2025_12_15_050928_fix_form_two_records_mode', 13),
(20, '2027_01_02_000100_update_form_two_records_unique_index', 14),
(21, '2027_01_15_000000_create_practice_periods_table', 15);

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
-- Структура таблицы `practice_periods`
--

CREATE TABLE `practice_periods` (
  `id` bigint UNSIGNED NOT NULL,
  `course` tinyint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `type` enum('educational','production') COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `room_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `hours_per_day` tinyint UNSIGNED NOT NULL DEFAULT '6',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `practice_periods`
--

INSERT INTO `practice_periods` (`id`, `course`, `group_id`, `type`, `teacher_id`, `room_id`, `start_date`, `end_date`, `hours_per_day`, `created_at`, `updated_at`) VALUES
(1, 2, 64, 'production', 77, NULL, '2026-01-09', '2026-01-16', 6, '2026-01-09 08:37:02', '2026-01-09 08:37:02');

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
-- Структура таблицы `second_course_group`
--

CREATE TABLE `second_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `second_course_group`
--

INSERT INTO `second_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `created_at`, `updated_at`) VALUES
(52, 'ТЭ-214', 214, NULL, NULL, NULL),
(53, 'М-214', 214, NULL, NULL, NULL),
(54, 'М-224', 224, NULL, NULL, NULL),
(55, 'М-234', 234, NULL, NULL, NULL),
(56, 'БКЕ-214', 214, NULL, NULL, NULL),
(57, 'БКЕ-224', 224, NULL, NULL, NULL),
(58, 'ПО-234', 234, NULL, NULL, NULL),
(59, 'ПО-244', 244, NULL, NULL, NULL),
(60, 'ПО-254', 254, NULL, NULL, NULL),
(61, 'ПО-264', 264, NULL, NULL, NULL),
(62, 'ПО-274', 274, NULL, NULL, NULL),
(63, 'ПО-284', 284, NULL, NULL, NULL),
(64, 'АКЖ-214', 214, NULL, NULL, NULL),
(65, 'СИБ-224', 224, NULL, NULL, NULL),
(66, 'СИБ-234', 234, NULL, NULL, NULL),
(67, 'СИБ-244', 244, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `second_course_schedules`
--

CREATE TABLE `second_course_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `replaces_schedule_id` bigint UNSIGNED DEFAULT NULL,
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
  `replacement_subject_id_1_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `is_absent_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `second_course_subjects`
--

CREATE TABLE `second_course_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `module_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_index` int DEFAULT NULL,
  `subject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kz` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `second_course_subjects`
--

INSERT INTO `second_course_subjects` (`id`, `module_title`, `module_index`, `subject_name`, `name_ru`, `name_kz`, `created_at`, `updated_at`) VALUES
(79, 'ООМ 01', 1, 'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни', 'Укреплять здоровье и соблюдать принципы здорового образа жизни', 'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(80, 'ООМ 02', 2, 'РО 2.1 Владеть основами информационно-коммуникационных технологий', 'Владеть основами информационно-коммуникационных технологий', 'Ақпараттық-коммуникациялық технологиялар негіздерін меңгеру', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(81, 'ООМ 02', 2, 'РО 2.2 Использовать услуги информационно-справочных и интерактивных веб-порталов', 'Использовать услуги информационно-справочных и интерактивных веб-порталов', 'Ақпараттық, анықтамалық және интерактивті веб-порталдардың қызметтерін пайдалану', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(82, 'ООМ 03', 3, 'РО 3.1 Владеть основными вопросами в области экономической теории', 'Владеть основными вопросами в области экономической теории', 'Экономикалық теория саласындағы негізгі мәселелерді білу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(83, 'ООМ 03', 3, 'РО 3.2 Создавать, поддерживать, контролировать и осуществлять постоянный мониторинг социальных сетей', 'Создавать, поддерживать, контролировать и осуществлять постоянный мониторинг социальных сетей', NULL, '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(84, 'ООМ 03', 3, 'РО 3.3 Владеть основными вопросами в области экономической теории', 'Владеть основными вопросами в области экономической теории', 'Экономикалық теория саласындағы негізгі мәселелерді білу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(85, 'ООМ 03', 3, 'РО 3.4 Анализировать и оценивать экономические процессы, происходящие на предприятии', 'Анализировать и оценивать экономические процессы, происходящие на предприятии', NULL, '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(86, 'ПМ 01', 1, 'РО 1.1 Производить монтаж сетевого и серверного оборудования, систем видеонаблюдения и систем контроля управления данными', 'Производить монтаж сетевого и серверного оборудования, систем видеонаблюдения и систем контроля управления данными', 'Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(87, 'ПМ 01', 1, 'РО 1.2 Конфигурировать сетевые сервисы и сетевое оборудование', 'Конфигурировать сетевые сервисы и сетевое оборудование', 'Желілік қызметтер мен желілік жабдықты конфигурациялау', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(88, 'ПМ 01', 1, 'РО 1.3 Разрабатывать дизайн виртуальных локаций (VR/AR/MR) и обеспечивать их редактирование', 'Разрабатывать дизайн виртуальных локаций (VR/AR/MR) и обеспечивать их редактирование', 'Виртуалды орындардың (VR/AR/MR) дизайнын әзірлеу және өңдеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(89, 'ПМ 01', 1, 'РО 1.4 Применять теории мотивации и корпоративной культуры', 'Применять теории мотивации и корпоративной культуры', 'Мотивация теориялары мен корпоративтік мәдениетті қолдану', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(90, 'ПМ 01', 1, 'РО 1.5 Автоматизировать задачи обслуживания информационных систем', 'Автоматизировать задачи обслуживания информационных систем', 'Ақпараттық жүйеге техникалық қызмет көрсету тапсырмаларын автоматтандыру', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(91, 'ПМ 02', 2, 'РО 2.1 Разрабатывать визуальное представление сайта', 'Разрабатывать визуальное представление сайта', 'Сайттың көрнекі презентациясын әзірлеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(92, 'ПМ 02', 2, 'РО 2.2 Разрабатывать функциональные возможности сайта', 'Разрабатывать функциональные возможности сайта', 'Сайттың функционалдық мүмкіндіктерін әзірлеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(93, 'ПМ 02', 2, 'РО 2.3 Администрировать Web-ресурсы', 'Администрировать Web-ресурсы', NULL, '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(94, 'ПМ 02', 2, 'РО 2.4 Применять терминологию на государственном языке при разработке и администрировании web-ресурсов', 'Применять терминологию на государственном языке при разработке и администрировании web-ресурсов', NULL, '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(95, 'ПМ 02', 2, 'РО 2.5 Применять иностранную терминологию при разработке и администрировании web-ресурсов', 'Применять иностранную терминологию при разработке и администрировании web-ресурсов', NULL, '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(96, 'ПМ 03', 3, 'РО 3.1 Разрабатывать программные решения на языках программирования', 'Разрабатывать программные решения на языках программирования', 'Бағдарламалық шешімдерді бағдарламалау тілдерінде әзірлеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(97, 'ПМ 03', 3, 'РО 3.2 Разрабатывать, внедрять и сопровождать программные решения автоматизированных информационных систем', 'Разрабатывать, внедрять и сопровождать программные решения автоматизированных информационных систем', 'Автоматтандырылған ақпараттық жүйелердің бағдарламалық шешімдерін әзірлеу, енгізу және сүйемелдеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(98, 'ПМ 03', 3, 'РО 3.3 Разрабатывать программные решения для мобильных устройств', 'Разрабатывать программные решения для мобильных устройств', 'Мобильді құрылғыларға арналған бағдарламалық шешімдерді әзірлеу', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(99, 'БМ 03', 3, 'РО 3.1 Владеть основными вопросами в области экономической теории', 'Владеть основными вопросами в области экономической теории', 'Экономикалық теория саласындағы негізгі мәселелерді меңгеру', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(100, 'БМ 03', 3, 'РО 3.1 Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', 'Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', 'Әлемдік экономиканың даму тенденцияларын, мемлекеттің \"жасыл\" экономикаға көшуінің негізгі міндеттерін түсіну', '2026-01-06 08:55:49', '2026-01-06 08:55:49'),
(101, 'Практика', NULL, 'Производственная практика', 'Производственная практика', NULL, '2026-01-09 08:37:02', '2026-01-09 08:37:02');

-- --------------------------------------------------------

--
-- Структура таблицы `second_course_teachers`
--

CREATE TABLE `second_course_teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `second_course_teachers`
--

INSERT INTO `second_course_teachers` (`id`, `teacher_name`, `created_at`, `updated_at`) VALUES
(76, 'Алданов Р.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(77, 'Алдажуманов Т.К.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(78, 'Альдекенов Т.С.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(79, 'Арыкова А.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(80, 'Ашимова А.К.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(81, 'Баширова Г.К.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(82, 'Бегембетов Д.М.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(83, 'Бралина М.Д.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(84, 'Брусенко В.С.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(85, 'Волочаева А.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(86, 'Григорьев Б.В.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(87, 'Жадрин А.Е.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(88, 'Жалпаков Т.Т.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(89, 'Жуматаева Р.К.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(90, 'Зейнолла А.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(91, 'Измайлова Е.В.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(92, 'Исаханова Ж.Г.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(93, 'Канагатова М.С.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(94, 'Кекина Е.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(95, 'Косбармаков А.Д.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(96, 'Крыжановский С.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(97, 'Қимадиден Г.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(98, 'Мирбеков Б.С.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(99, 'Мухамеджанова К.Б.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(100, 'Мынгышева А.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(101, 'Нестеров И.Ю.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(102, 'Нурмагамбетова Л.Б.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(103, 'Окенов Р.Н.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(104, 'Олейник С.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(105, 'Пилипенко А.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(106, 'Серёгина Е.А.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(107, 'Смурыгин А.М.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(108, 'Сулейменова К.М.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(109, 'Ташимов Д.К.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(110, 'Тауымова А.Е.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(111, 'Тетерина С.В.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(112, 'Хайпергина А.Ю.', '2026-01-05 10:41:37', '2026-01-05 10:41:37'),
(113, 'Шамгунова А.Е.', '2026-01-05 10:41:37', '2026-01-05 10:41:37');

-- --------------------------------------------------------

--
-- Структура таблицы `second_form_two_normatives`
--

CREATE TABLE `second_form_two_normatives` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `second_form_two_records`
--

CREATE TABLE `second_form_two_records` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `day` tinyint UNSIGNED DEFAULT NULL,
  `class_date` date DEFAULT NULL,
  `lesson_number` tinyint UNSIGNED DEFAULT NULL,
  `subgroup` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `status` enum('normal','sick','replacement','replaced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id` bigint UNSIGNED DEFAULT NULL,
  `bonus_hours` int DEFAULT NULL,
  `used_hours` int NOT NULL DEFAULT '0',
  `absent_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replacement_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('AF2oGercYpkBohe7XLcKttqokqaPUlTxjP8QitEr', NULL, '172.31.0.1', 'Mozilla/5.0 (X11; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieUU2eHBmMXBHOTY4MW1sZzJxWjhHWEpHOGZVOEtsUkZpbzlqRUhQZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9maXJzdC1jb3Vyc2UvZm9ybS10d28/Y291cnNlPTEmZ3JvdXBfaWQ9MTImbW9udGg9OSZ5ZWFyPTIwMjUiO3M6NToicm91dGUiO3M6MjM6ImZpcnN0LnNjaGVkdWxlLmZvcm1fdHdvIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768371081);

-- --------------------------------------------------------

--
-- Структура таблицы `third_course_group`
--

CREATE TABLE `third_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `third_course_group`
--

INSERT INTO `third_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `created_at`, `updated_at`) VALUES
(1, 'ТЭ-313', 313, NULL, NULL, NULL),
(2, 'ТЭ-323', 323, NULL, NULL, NULL),
(3, 'М-313', 313, NULL, NULL, NULL),
(4, 'М-323', 323, NULL, NULL, NULL),
(5, 'М-333', 333, NULL, NULL, NULL),
(6, 'М-343', 343, NULL, NULL, NULL),
(7, 'БКЕ-313', 313, NULL, NULL, NULL),
(8, 'БКЕ-323', 323, NULL, NULL, NULL),
(9, 'БКЕ-333', 333, NULL, NULL, NULL),
(10, 'ПО-303', 303, NULL, NULL, NULL),
(11, 'ПО-313', 313, NULL, NULL, NULL),
(12, 'ПО-323', 323, NULL, NULL, NULL),
(13, 'ПО-333', 333, NULL, NULL, NULL),
(14, 'ПО-343', 343, NULL, NULL, NULL),
(15, 'ПО-353', 353, NULL, NULL, NULL),
(16, 'ПО-363', 363, NULL, NULL, NULL),
(17, 'ПО-373', 373, NULL, NULL, NULL),
(18, 'ПО-383', 383, NULL, NULL, NULL),
(19, 'ПО-393', 393, NULL, NULL, NULL),
(20, 'АҚЖ-313', 313, NULL, NULL, NULL),
(21, 'СИБ-313', 313, NULL, NULL, NULL),
(22, 'СИБ-323', 323, NULL, NULL, NULL),
(23, 'СИБ-333', 333, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `third_course_schedules`
--

CREATE TABLE `third_course_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `replaces_schedule_id` bigint UNSIGNED DEFAULT NULL,
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
  `replacement_subject_id_1_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator` bigint UNSIGNED DEFAULT NULL,
  `is_absent_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_1_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_1_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_1_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_denominator_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_den` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_den` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_den` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id_2` bigint UNSIGNED DEFAULT NULL,
  `is_absent_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `is_replacement_2_num` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_teacher_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id_2_num` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment_2_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replacement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `third_course_subjects`
--

CREATE TABLE `third_course_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `module_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_index` int DEFAULT NULL,
  `subject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kz` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `third_course_subjects`
--

INSERT INTO `third_course_subjects` (`id`, `module_title`, `module_index`, `subject_name`, `name_ru`, `name_kz`, `created_at`, `updated_at`) VALUES
(34, 'БМ01', 1, 'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни', 'Укреплять здоровье и соблюдать принципы здорового образа жизни', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(35, 'БМ01', 1, 'РО 1.2 Совершенствовать физические качества и психофизиологические способности', 'Совершенствовать физические качества и психофизиологические способности', 'Дене қасиеттері мен психофизиологиялық қабілеттерді жетілдіру', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(36, 'БМ04', 4, 'РО 4.1 Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию', 'Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию', 'Төзімді және белсенді жеке ұстанымды қалыптастыратын моральдық-адамгершілік құндылықтар мен нормаларды түсіну', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(37, 'БМ04', 4, 'РО 4.2 Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации', 'Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации', 'Әлемдік өркениеттегі Қазақстан Республикасы халықтары мәдениетінің рөлі мен орнын түсіну', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(38, 'БМ04', 4, 'РО 4.3 Владеть сведениями об основных отраслях права', 'Владеть сведениями об основных отраслях права', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(39, 'БМ04', 4, 'РО 4.4 Владеть основными понятиями социологии и политологии', 'Владеть основными понятиями социологии и политологии', 'Әлеуметтану мен саясаттанудың негізгі түсініктерін меңгеру', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(40, 'ПМ04', 4, 'РО 4.1 Обеспечивать бесперебойную работу программного и аппаратного обеспечения', 'Обеспечивать бесперебойную работу программного и аппаратного обеспечения', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(41, 'ПМ04', 4, 'РО 4.2 Проводить поиск, подбор и отбор персонала', 'Проводить поиск, подбор и отбор персонала', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(42, 'ПМ04', 4, 'РО 4.2 Реагировать на инциденты информационной безопасности', 'Реагировать на инциденты информационной безопасности', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(43, 'ПМ04', 4, 'РО 4.3 Производить основные технико-экономические расчеты по ремонту, монтажу, обслуживанию электромеханического оборудования предприятия', 'Производить основные технико-экономические расчеты по ремонту, монтажу, обслуживанию электромеханического оборудования предприятия', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(44, 'ПМ04', 4, 'РО 4.3 Использовать возможности программного и аппаратного обеспечения', 'Использовать возможности программного и аппаратного обеспечения', 'Бағдарламалық және аппараттық мүмкіндіктерді пайдалану', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(45, 'ПМ04', 4, 'РО 4.3 Планировать процессы управления и обеспечения информационной безопасности организации', 'Планировать процессы управления и обеспечения информационной безопасности организации', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(46, 'ПМ04', 4, 'РО 4.3 Участвовать в проведении анализа трудовых процессов', 'Участвовать в проведении анализа трудовых процессов', 'Еңбек процестеріне талдау жүргізуге қатысу', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(47, 'ПМ04', 4, 'РО 4.4 Осуществлять постановку задач персоналу, занимающемуся продажами, в том числе через Интернет', 'Осуществлять постановку задач персоналу, занимающемуся продажами, в том числе через Интернет', 'Сатумен айналысатын қызметкерлерге, оның ішінде Интернет арқылы міндеттер қоюды жүзеге асыру', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(48, 'ПМ04', 4, 'РО 4.4 Контролировать процессы управления и обеспечения информационной безопасности организации', 'Контролировать процессы управления и обеспечения информационной безопасности организации', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(49, 'ПМ04', 4, 'РО 4.4 Разрабатывать мероприятия по повышению качества ремонта электромеханического оборудования', 'Разрабатывать мероприятия по повышению качества ремонта электромеханического оборудования', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(50, 'ПМ04', 4, 'РО 4.4 Координировать работу команды', 'Координировать работу команды', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(51, 'ПМ04', 4, 'РО 4.5 Тестировать аппаратно-программные средства обеспечения информационной безопасности', 'Тестировать аппаратно-программные средства обеспечения информационной безопасности', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(52, 'ПМ04', 4, 'РО 4.6 Восстанавливать работоспособность аппаратно-программных средств обеспечения информационной безопасности', 'Восстанавливать работоспособность аппаратно-программных средств обеспечения информационной безопасности', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(53, 'ПМ04', 4, 'РО 4.7 Анализировать защищенность, проектировать и создавать безопасные конфигурации информационной системы; расследование инцидентов', 'Анализировать защищенность, проектировать и создавать безопасные конфигурации информационной системы; расследование инцидентов', 'Қауіпсіздікті талдау, ақпараттық жүйенің қауіпсіз конфигурацияларын жобалау және құру, оқиғаларды зерттеу', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(54, 'ПМ05', 5, 'РО 5.1 Производить монтаж локально вычислительной сети организации', 'Производить монтаж локально вычислительной сети организации', 'Ұйымның жергілікті есептеу желісін орнату', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(55, 'ПМ05', 5, 'РО 5.2 Производить монтаж серверного оборудования организации', 'Производить монтаж серверного оборудования организации', 'Ұйымның серверлік жабдығын монтаждау', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(56, 'ПМ05', 5, 'РО 5.2 Составлять графики организации ремонта, наладки и обслуживания электрооборудования для структурного подразделения в соответствии с экологическими, архитектурными и нормативными требованиями', 'Составлять графики организации ремонта, наладки и обслуживания электрооборудования для структурного подразделения в соответствии с экологическими, архитектурными и нормативными требованиями', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(57, 'ПМ06', 6, 'РО 6.1 Разрабатывать программный код программного обеспечения по готовым спецификациям требований к программному обеспечению', 'Разрабатывать программный код программного обеспечения по готовым спецификациям требований к программному обеспечению', 'Бағдарламалық қамтамасыздандырудың дайын талаптарына сәйкес бағдарламалық қамтамасыздандыру кодын әзірлеу', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(58, 'ПМ06', 6, 'РО 6.3 Проводить и выполнять наладку релейной защиты и автоматики', 'Проводить и выполнять наладку релейной защиты и автоматики', NULL, '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(59, 'КМ04', 4, 'ОН 4.1 Управлять механизмами безопасности', 'Управлять механизмами безопасности', 'Қауіпсіздік тетіктерін басқару', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(60, 'КМ04', 4, 'ОН 4.2 Реагировать на инциденты информационной безопасности', 'Реагировать на инциденты информационной безопасности', 'Ақпараттық қауіпсіздік инциденттеріне әрекет ету', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(61, 'КМ04', 4, 'ОН 4.3 Использовать возможности программного и аппаратного обеспечения', 'Использовать возможности программного и аппаратного обеспечения', 'Бағдарламалық және аппараттық мүмкіндіктерді пайдалану', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(62, 'КМ04', 4, 'ОН 4.3 Участвовать в проведении анализа трудовых процессов', 'Участвовать в проведении анализа трудовых процессов', 'Еңбек процестеріне талдау жүргізуге қатысу', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(63, 'КМ04', 4, 'ОН 4.4 Контролировать процесс управления и обеспечения информационной безопасности организации', 'Контролировать процесс управления и обеспечения информационной безопасности организации', 'Ұйымның ақпараттық қауіпсіздігін басқару және қамтамасыз ету үрдісін бақылау', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(64, 'КМ05', 5, 'ОН 5.1 Производить монтаж локально-вычислительной сети организации', 'Производить монтаж локально-вычислительной сети организации', 'Ұйымның жергілікті есептеу желісін орнату', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(65, 'КМ05', 5, 'ОН 5.2 Производить монтаж серверного оборудования организации', 'Производить монтаж серверного оборудования организации', 'Ұйымның серверлік жабдығын монтаждау', '2026-01-06 08:57:59', '2026-01-06 08:57:59'),
(66, 'КМ06', 6, 'ОН 6.1 Разрабатывать код программного обеспечения в соответствии с готовыми требованиями к программному обеспечению', 'Разрабатывать код программного обеспечения в соответствии с готовыми требованиями к программному обеспечению', 'Бағдарламалық қамтамасыздандырудың дайын талаптарына сәйкес бағдарламалық қамтамасыздандыру кодын әзірлеу', '2026-01-06 08:57:59', '2026-01-06 08:57:59');

-- --------------------------------------------------------

--
-- Структура таблицы `third_course_teachers`
--

CREATE TABLE `third_course_teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `third_course_teachers`
--

INSERT INTO `third_course_teachers` (`id`, `teacher_name`, `created_at`, `updated_at`) VALUES
(1, 'Аяпберген Н.Е.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(2, 'Айткенова А.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(3, 'Альдекенов Т.С.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(4, 'Алданов Р.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(5, 'Арыкова А.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(6, 'Ашимова А.К.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(7, 'Бегембетов Д.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(8, 'Брусенко В.С.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(9, 'Григорьев Б.В.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(10, 'Жадрин А.Е.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(11, 'Жалпаков Т.Т.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(12, 'Зейнолла А.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(13, 'Исаханова Ж.Г.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(14, 'Канагатова М.С.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(15, 'Кекина Е.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(16, 'Ксембаева Д.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(17, 'Крыжановский С.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(18, 'Льясова А.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(19, 'Мирбеков Б.С.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(20, 'Смурыгин А.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(21, 'Солтанова А.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(22, 'Сулейменова К.М.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(23, 'Серёгина Е.А.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(24, 'Табулдинов Б.К.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(25, 'Ташимов Д.К.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(26, 'Тетерина С.В.', '2026-01-05 10:44:35', '2026-01-05 10:44:35'),
(27, 'Шамгунова А.Е.', '2026-01-05 10:44:35', '2026-01-05 10:44:35');

-- --------------------------------------------------------

--
-- Структура таблицы `third_form_two_normatives`
--

CREATE TABLE `third_form_two_normatives` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `third_form_two_records`
--

CREATE TABLE `third_form_two_records` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `day` tinyint UNSIGNED DEFAULT NULL,
  `class_date` date DEFAULT NULL,
  `lesson_number` tinyint UNSIGNED DEFAULT NULL,
  `subgroup` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `total_hours` int NOT NULL DEFAULT '0',
  `hours_per_class` int NOT NULL DEFAULT '2',
  `status` enum('normal','sick','replacement','replaced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_subject_id` bigint UNSIGNED DEFAULT NULL,
  `bonus_hours` int DEFAULT NULL,
  `used_hours` int NOT NULL DEFAULT '0',
  `absent_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replacement_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD KEY `first_course_schedules_group_week_idx` (`group_id`,`week_start`),
  ADD KEY `first_course_schedules_replaces_schedule_id_foreign` (`replaces_schedule_id`);

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
  ADD UNIQUE KEY `unique_record_full` (`group_id`,`year`,`month`,`day`,`lesson_number`,`subject_id`,`mode`,`subgroup`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_replacement_teacher` (`replacement_teacher_id`),
  ADD KEY `form2_group_date_lesson_mode_idx` (`group_id`,`class_date`,`lesson_number`,`subgroup`,`mode`);

--
-- Индексы таблицы `fourth_course_group`
--
ALTER TABLE `fourth_course_group`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fourth_course_schedules`
--
ALTER TABLE `fourth_course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fourth_course_schedules_subject_id_foreign` (`subject_id`),
  ADD KEY `fourth_course_schedules_subject_id_denominator_foreign` (`subject_id_denominator`),
  ADD KEY `fourth_course_schedules_subject_id_denominator_2_foreign` (`subject_id_denominator_2`),
  ADD KEY `fourth_course_schedules_subject_id_2_foreign` (`subject_id_2`),
  ADD KEY `fourth_course_schedules_teacher_id_foreign` (`teacher_id`),
  ADD KEY `fourth_course_schedules_teacher_id_denominator_foreign` (`teacher_id_denominator`),
  ADD KEY `fourth_course_schedules_teacher_id_denominator_2_foreign` (`teacher_id_denominator_2`),
  ADD KEY `fourth_course_schedules_teacher_id_2_foreign` (`teacher_id_2`),
  ADD KEY `fourth_course_schedules_replacement_teacher_id_1_num_foreign` (`replacement_teacher_id_1_num`),
  ADD KEY `fourth_course_schedules_replacement_subject_id_1_num_foreign` (`replacement_subject_id_1_num`),
  ADD KEY `fourth_course_schedules_replacement_teacher_id_1_den_foreign` (`replacement_teacher_id_1_den`),
  ADD KEY `fourth_course_schedules_replacement_subject_id_1_den_foreign` (`replacement_subject_id_1_den`),
  ADD KEY `fourth_course_schedules_replacement_teacher_id_2_den_foreign` (`replacement_teacher_id_2_den`),
  ADD KEY `fourth_course_schedules_replacement_subject_id_2_den_foreign` (`replacement_subject_id_2_den`),
  ADD KEY `fourth_course_schedules_replacement_teacher_id_2_num_foreign` (`replacement_teacher_id_2_num`),
  ADD KEY `fourth_course_schedules_replacement_subject_id_2_num_foreign` (`replacement_subject_id_2_num`),
  ADD KEY `fourth_course_schedules_group_week_idx` (`group_id`,`week_start`),
  ADD KEY `fourth_course_schedules_room_idx` (`room_id`,`study_day`,`lesson_number`),
  ADD KEY `fourth_course_schedules_room_den_idx` (`room_id_denominator`,`study_day`,`lesson_number`),
  ADD KEY `fourth_course_schedules_week_start_index` (`week_start`);

--
-- Индексы таблицы `fourth_course_subjects`
--
ALTER TABLE `fourth_course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fourth_course_teachers`
--
ALTER TABLE `fourth_course_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fourth_form_two_normatives`
--
ALTER TABLE `fourth_form_two_normatives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fourth_form_two_normatives_uniq` (`group_id`,`subject_id`,`teacher_id`,`month`,`year`),
  ADD KEY `fourth_form_two_normatives_subject_id_foreign` (`subject_id`),
  ADD KEY `fourth_form_two_normatives_teacher_id_foreign` (`teacher_id`);

--
-- Индексы таблицы `fourth_form_two_records`
--
ALTER TABLE `fourth_form_two_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fourth_form_two_records_subject_id_foreign` (`subject_id`),
  ADD KEY `fourth_form_two_records_teacher_id_foreign` (`teacher_id`),
  ADD KEY `fourth_form_two_records_replacement_teacher_id_foreign` (`replacement_teacher_id`),
  ADD KEY `fourth_form_two_records_replacement_subject_id_foreign` (`replacement_subject_id`),
  ADD KEY `fourth_form_two_records_date_idx` (`group_id`,`class_date`,`lesson_number`,`subgroup`,`mode`);

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
-- Индексы таблицы `practice_periods`
--
ALTER TABLE `practice_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_periods_course_group_idx` (`course`,`group_id`,`start_date`,`end_date`);

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
-- Индексы таблицы `second_course_group`
--
ALTER TABLE `second_course_group`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `second_course_schedules`
--
ALTER TABLE `second_course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `second_course_schedules_subject_id_foreign` (`subject_id`),
  ADD KEY `second_course_schedules_subject_id_denominator_foreign` (`subject_id_denominator`),
  ADD KEY `second_course_schedules_subject_id_denominator_2_foreign` (`subject_id_denominator_2`),
  ADD KEY `second_course_schedules_subject_id_2_foreign` (`subject_id_2`),
  ADD KEY `second_course_schedules_teacher_id_foreign` (`teacher_id`),
  ADD KEY `second_course_schedules_teacher_id_denominator_foreign` (`teacher_id_denominator`),
  ADD KEY `second_course_schedules_teacher_id_denominator_2_foreign` (`teacher_id_denominator_2`),
  ADD KEY `second_course_schedules_teacher_id_2_foreign` (`teacher_id_2`),
  ADD KEY `second_course_schedules_replacement_teacher_id_1_num_foreign` (`replacement_teacher_id_1_num`),
  ADD KEY `second_course_schedules_replacement_subject_id_1_num_foreign` (`replacement_subject_id_1_num`),
  ADD KEY `second_course_schedules_replacement_teacher_id_1_den_foreign` (`replacement_teacher_id_1_den`),
  ADD KEY `second_course_schedules_replacement_subject_id_1_den_foreign` (`replacement_subject_id_1_den`),
  ADD KEY `second_course_schedules_replacement_teacher_id_2_den_foreign` (`replacement_teacher_id_2_den`),
  ADD KEY `second_course_schedules_replacement_subject_id_2_den_foreign` (`replacement_subject_id_2_den`),
  ADD KEY `second_course_schedules_replacement_teacher_id_2_num_foreign` (`replacement_teacher_id_2_num`),
  ADD KEY `second_course_schedules_replacement_subject_id_2_num_foreign` (`replacement_subject_id_2_num`),
  ADD KEY `second_course_schedules_group_week_idx` (`group_id`,`week_start`),
  ADD KEY `second_course_schedules_room_idx` (`room_id`,`study_day`,`lesson_number`),
  ADD KEY `second_course_schedules_room_den_idx` (`room_id_denominator`,`study_day`,`lesson_number`),
  ADD KEY `second_course_schedules_week_start_index` (`week_start`);

--
-- Индексы таблицы `second_course_subjects`
--
ALTER TABLE `second_course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `second_course_teachers`
--
ALTER TABLE `second_course_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `second_form_two_normatives`
--
ALTER TABLE `second_form_two_normatives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `second_form_two_normatives_uniq` (`group_id`,`subject_id`,`teacher_id`,`month`,`year`),
  ADD KEY `second_form_two_normatives_subject_id_foreign` (`subject_id`),
  ADD KEY `second_form_two_normatives_teacher_id_foreign` (`teacher_id`);

--
-- Индексы таблицы `second_form_two_records`
--
ALTER TABLE `second_form_two_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `second_form_two_records_subject_id_foreign` (`subject_id`),
  ADD KEY `second_form_two_records_teacher_id_foreign` (`teacher_id`),
  ADD KEY `second_form_two_records_replacement_teacher_id_foreign` (`replacement_teacher_id`),
  ADD KEY `second_form_two_records_replacement_subject_id_foreign` (`replacement_subject_id`),
  ADD KEY `second_form_two_records_date_idx` (`group_id`,`class_date`,`lesson_number`,`subgroup`,`mode`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Индексы таблицы `third_course_group`
--
ALTER TABLE `third_course_group`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `third_course_schedules`
--
ALTER TABLE `third_course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `third_course_schedules_subject_id_foreign` (`subject_id`),
  ADD KEY `third_course_schedules_subject_id_denominator_foreign` (`subject_id_denominator`),
  ADD KEY `third_course_schedules_subject_id_denominator_2_foreign` (`subject_id_denominator_2`),
  ADD KEY `third_course_schedules_subject_id_2_foreign` (`subject_id_2`),
  ADD KEY `third_course_schedules_teacher_id_foreign` (`teacher_id`),
  ADD KEY `third_course_schedules_teacher_id_denominator_foreign` (`teacher_id_denominator`),
  ADD KEY `third_course_schedules_teacher_id_denominator_2_foreign` (`teacher_id_denominator_2`),
  ADD KEY `third_course_schedules_teacher_id_2_foreign` (`teacher_id_2`),
  ADD KEY `third_course_schedules_replacement_teacher_id_1_num_foreign` (`replacement_teacher_id_1_num`),
  ADD KEY `third_course_schedules_replacement_subject_id_1_num_foreign` (`replacement_subject_id_1_num`),
  ADD KEY `third_course_schedules_replacement_teacher_id_1_den_foreign` (`replacement_teacher_id_1_den`),
  ADD KEY `third_course_schedules_replacement_subject_id_1_den_foreign` (`replacement_subject_id_1_den`),
  ADD KEY `third_course_schedules_replacement_teacher_id_2_den_foreign` (`replacement_teacher_id_2_den`),
  ADD KEY `third_course_schedules_replacement_subject_id_2_den_foreign` (`replacement_subject_id_2_den`),
  ADD KEY `third_course_schedules_replacement_teacher_id_2_num_foreign` (`replacement_teacher_id_2_num`),
  ADD KEY `third_course_schedules_replacement_subject_id_2_num_foreign` (`replacement_subject_id_2_num`),
  ADD KEY `third_course_schedules_group_week_idx` (`group_id`,`week_start`),
  ADD KEY `third_course_schedules_room_idx` (`room_id`,`study_day`,`lesson_number`),
  ADD KEY `third_course_schedules_room_den_idx` (`room_id_denominator`,`study_day`,`lesson_number`),
  ADD KEY `third_course_schedules_week_start_index` (`week_start`);

--
-- Индексы таблицы `third_course_subjects`
--
ALTER TABLE `third_course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `third_course_teachers`
--
ALTER TABLE `third_course_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `third_form_two_normatives`
--
ALTER TABLE `third_form_two_normatives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `third_form_two_normatives_uniq` (`group_id`,`subject_id`,`teacher_id`,`month`,`year`),
  ADD KEY `third_form_two_normatives_subject_id_foreign` (`subject_id`),
  ADD KEY `third_form_two_normatives_teacher_id_foreign` (`teacher_id`);

--
-- Индексы таблицы `third_form_two_records`
--
ALTER TABLE `third_form_two_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `third_form_two_records_subject_id_foreign` (`subject_id`),
  ADD KEY `third_form_two_records_teacher_id_foreign` (`teacher_id`),
  ADD KEY `third_form_two_records_replacement_teacher_id_foreign` (`replacement_teacher_id`),
  ADD KEY `third_form_two_records_replacement_subject_id_foreign` (`replacement_subject_id`),
  ADD KEY `third_form_two_records_date_idx` (`group_id`,`class_date`,`lesson_number`,`subgroup`,`mode`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT для таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=557;

--
-- AUTO_INCREMENT для таблицы `fourth_course_group`
--
ALTER TABLE `fourth_course_group`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `fourth_course_schedules`
--
ALTER TABLE `fourth_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fourth_course_subjects`
--
ALTER TABLE `fourth_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fourth_course_teachers`
--
ALTER TABLE `fourth_course_teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fourth_form_two_normatives`
--
ALTER TABLE `fourth_form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fourth_form_two_records`
--
ALTER TABLE `fourth_form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `practice_periods`
--
ALTER TABLE `practice_periods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT для таблицы `second_course_group`
--
ALTER TABLE `second_course_group`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT для таблицы `second_course_schedules`
--
ALTER TABLE `second_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `second_course_subjects`
--
ALTER TABLE `second_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT для таблицы `second_course_teachers`
--
ALTER TABLE `second_course_teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT для таблицы `second_form_two_normatives`
--
ALTER TABLE `second_form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `second_form_two_records`
--
ALTER TABLE `second_form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `third_course_group`
--
ALTER TABLE `third_course_group`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT для таблицы `third_course_schedules`
--
ALTER TABLE `third_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `third_course_subjects`
--
ALTER TABLE `third_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT для таблицы `third_course_teachers`
--
ALTER TABLE `third_course_teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `third_form_two_normatives`
--
ALTER TABLE `third_form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `third_form_two_records`
--
ALTER TABLE `third_form_two_records`
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
-- Ограничения внешнего ключа таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  ADD CONSTRAINT `first_course_schedules_replaces_schedule_id_foreign` FOREIGN KEY (`replaces_schedule_id`) REFERENCES `first_course_schedules` (`id`) ON DELETE SET NULL;

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
-- Ограничения внешнего ключа таблицы `fourth_course_schedules`
--
ALTER TABLE `fourth_course_schedules`
  ADD CONSTRAINT `fourth_course_schedules_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_1_den_foreign` FOREIGN KEY (`replacement_subject_id_1_den`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_1_num_foreign` FOREIGN KEY (`replacement_subject_id_1_num`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_2_den_foreign` FOREIGN KEY (`replacement_subject_id_2_den`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_2_num_foreign` FOREIGN KEY (`replacement_subject_id_2_num`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_teacher_id_1_den_foreign` FOREIGN KEY (`replacement_teacher_id_1_den`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_teacher_id_1_num_foreign` FOREIGN KEY (`replacement_teacher_id_1_num`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_teacher_id_2_den_foreign` FOREIGN KEY (`replacement_teacher_id_2_den`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_teacher_id_2_num_foreign` FOREIGN KEY (`replacement_teacher_id_2_num`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_teacher_id_2_foreign` FOREIGN KEY (`teacher_id_2`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_teacher_id_denominator_2_foreign` FOREIGN KEY (`teacher_id_denominator_2`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_teacher_id_denominator_foreign` FOREIGN KEY (`teacher_id_denominator`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `fourth_form_two_normatives`
--
ALTER TABLE `fourth_form_two_normatives`
  ADD CONSTRAINT `fourth_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_form_two_normatives_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `fourth_form_two_records`
--
ALTER TABLE `fourth_form_two_records`
  ADD CONSTRAINT `fourth_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_form_two_records_replacement_teacher_id_foreign` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_form_two_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `fourth_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `schedule_replacements`
--
ALTER TABLE `schedule_replacements`
  ADD CONSTRAINT `fk_repl_absent` FOREIGN KEY (`absent_teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_repl_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_repl_replacement` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `frist_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_repl_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `second_course_schedules`
--
ALTER TABLE `second_course_schedules`
  ADD CONSTRAINT `second_course_schedules_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `second_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_course_schedules_replacement_subject_id_1_den_foreign` FOREIGN KEY (`replacement_subject_id_1_den`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_subject_id_1_num_foreign` FOREIGN KEY (`replacement_subject_id_1_num`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_subject_id_2_den_foreign` FOREIGN KEY (`replacement_subject_id_2_den`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_subject_id_2_num_foreign` FOREIGN KEY (`replacement_subject_id_2_num`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_teacher_id_1_den_foreign` FOREIGN KEY (`replacement_teacher_id_1_den`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_teacher_id_1_num_foreign` FOREIGN KEY (`replacement_teacher_id_1_num`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_teacher_id_2_den_foreign` FOREIGN KEY (`replacement_teacher_id_2_den`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_replacement_teacher_id_2_num_foreign` FOREIGN KEY (`replacement_teacher_id_2_num`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_teacher_id_2_foreign` FOREIGN KEY (`teacher_id_2`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_teacher_id_denominator_2_foreign` FOREIGN KEY (`teacher_id_denominator_2`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_teacher_id_denominator_foreign` FOREIGN KEY (`teacher_id_denominator`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `second_form_two_normatives`
--
ALTER TABLE `second_form_two_normatives`
  ADD CONSTRAINT `second_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `second_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_form_two_normatives_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `second_form_two_records`
--
ALTER TABLE `second_form_two_records`
  ADD CONSTRAINT `second_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `second_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_form_two_records_replacement_teacher_id_foreign` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_form_two_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `second_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `third_course_schedules`
--
ALTER TABLE `third_course_schedules`
  ADD CONSTRAINT `third_course_schedules_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_1_den_foreign` FOREIGN KEY (`replacement_subject_id_1_den`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_1_num_foreign` FOREIGN KEY (`replacement_subject_id_1_num`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_2_den_foreign` FOREIGN KEY (`replacement_subject_id_2_den`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_2_num_foreign` FOREIGN KEY (`replacement_subject_id_2_num`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_teacher_id_1_den_foreign` FOREIGN KEY (`replacement_teacher_id_1_den`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_teacher_id_1_num_foreign` FOREIGN KEY (`replacement_teacher_id_1_num`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_teacher_id_2_den_foreign` FOREIGN KEY (`replacement_teacher_id_2_den`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_teacher_id_2_num_foreign` FOREIGN KEY (`replacement_teacher_id_2_num`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_teacher_id_2_foreign` FOREIGN KEY (`teacher_id_2`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_teacher_id_denominator_2_foreign` FOREIGN KEY (`teacher_id_denominator_2`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_teacher_id_denominator_foreign` FOREIGN KEY (`teacher_id_denominator`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `third_form_two_normatives`
--
ALTER TABLE `third_form_two_normatives`
  ADD CONSTRAINT `third_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_form_two_normatives_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `third_form_two_records`
--
ALTER TABLE `third_form_two_records`
  ADD CONSTRAINT `third_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_form_two_records_replacement_teacher_id_foreign` FOREIGN KEY (`replacement_teacher_id`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_form_two_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `third_course_teachers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
