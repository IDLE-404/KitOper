-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Янв 26 2026 г., 08:23
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
  `has_subgroups` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_type` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_group`
--

INSERT INTO `first_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `has_subgroups`, `created_at`, `updated_at`, `group_type`) VALUES
(1, 'ТЭ-115', 115, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(2, 'БҚЕ-115', 115, NULL, 1, NULL, '2026-01-16 08:45:33', 'kz'),
(3, 'БҚЕ-125', 125, NULL, 1, NULL, '2026-01-16 08:45:33', 'kz'),
(4, 'БҚЕ-135', 135, NULL, 1, NULL, '2026-01-16 08:45:33', 'kz'),
(5, 'ПО-115', 115, NULL, 0, NULL, '2026-01-20 03:39:10', 'ru'),
(6, 'ПО-145', 145, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(7, 'ПО-155', 155, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(8, 'ПО-165', 165, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(9, 'ПО-175', 175, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(10, 'ПО-185', 185, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(11, 'ПО-195', 195, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(12, 'АҚЖ-115', 115, NULL, 1, NULL, '2026-01-16 08:45:33', 'kz'),
(13, 'АҚЖ-125', 125, NULL, 0, NULL, '2026-01-20 04:06:43', 'kz'),
(14, 'СИБ-135', 135, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(15, 'СИБ-145', 145, NULL, 1, NULL, '2026-01-16 09:04:22', 'ru'),
(16, 'М-115', 115, NULL, 1, NULL, '2026-01-16 08:45:33', 'kz'),
(17, 'М-125', 125, NULL, 1, NULL, '2026-01-20 04:16:06', 'ru'),
(18, 'М-135', 135, NULL, 1, NULL, '2026-01-23 06:53:26', 'ru');

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
(5, NULL, '2026-02-02', 'Понедельник', 2, 1, 2, 4, NULL, NULL, 9, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(6, NULL, '2026-02-02', 'Понедельник', 2, 1, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(7, NULL, '2026-02-02', 'Среда', 4, 1, 14, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(8, NULL, '2026-02-02', 'Пятница', 1, 1, 10, 16, NULL, NULL, 1, 65, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(9, NULL, '2026-02-09', 'Понедельник', 2, 1, 2, 4, NULL, NULL, 9, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(10, NULL, '2026-02-09', 'Понедельник', 2, 1, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(11, NULL, '2026-02-09', 'Среда', 4, 1, 14, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(12, NULL, '2026-02-09', 'Пятница', 1, 1, 10, 16, NULL, NULL, 1, 65, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(19, NULL, '2026-02-02', 'Понедельник', 1, 2, 31, 10, NULL, NULL, 57, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(20, NULL, '2026-02-02', 'Вторник', 3, 2, 29, 14, NULL, NULL, 71, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(21, NULL, '2026-02-02', 'Четверг', 3, 2, 4, 8, NULL, NULL, 42, 72, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(22, NULL, '2026-02-02', 'Четверг', 3, 2, 4, NULL, 8, NULL, 24, NULL, 70, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(23, NULL, '2026-02-09', 'Понедельник', 1, 2, 31, 10, NULL, NULL, 57, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(24, NULL, '2026-02-09', 'Вторник', 3, 2, 29, 14, NULL, NULL, 71, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(25, NULL, '2026-02-09', 'Четверг', 3, 2, 4, 8, NULL, NULL, 42, 72, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(26, NULL, '2026-02-09', 'Четверг', 3, 2, 4, NULL, 8, NULL, 24, NULL, 70, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(27, NULL, '2026-02-02', 'Вторник', 2, 3, 31, 4, NULL, NULL, 50, 42, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(28, NULL, '2026-02-02', 'Вторник', 2, 3, NULL, NULL, 4, NULL, NULL, NULL, 17, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(29, NULL, '2026-02-02', 'Среда', 1, 3, 8, 10, NULL, NULL, 33, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(30, NULL, '2026-02-02', 'Среда', 1, 3, 8, NULL, NULL, NULL, 70, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(31, NULL, '2026-02-02', 'Четверг', 2, 3, 29, 14, NULL, NULL, 71, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(32, NULL, '2026-02-09', 'Вторник', 2, 3, 31, 4, NULL, NULL, 50, 42, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(33, NULL, '2026-02-09', 'Вторник', 2, 3, NULL, NULL, 4, NULL, NULL, NULL, 17, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(34, NULL, '2026-02-09', 'Среда', 1, 3, 8, 10, NULL, NULL, 33, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(35, NULL, '2026-02-09', 'Среда', 1, 3, 8, NULL, NULL, NULL, 70, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(36, NULL, '2026-02-09', 'Четверг', 2, 3, 29, 14, NULL, NULL, 71, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(37, NULL, '2026-02-02', 'Вторник', 2, 4, 10, 14, NULL, NULL, 21, 31, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(38, NULL, '2026-02-02', 'Среда', 2, 4, 8, 29, NULL, NULL, 33, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(39, NULL, '2026-02-02', 'Среда', 2, 4, 8, NULL, NULL, NULL, 48, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(40, NULL, '2026-02-02', 'Четверг', 1, 4, 31, 4, NULL, NULL, 50, 42, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(41, NULL, '2026-02-02', 'Четверг', 1, 4, NULL, NULL, 4, NULL, NULL, NULL, 17, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(42, NULL, '2026-02-09', 'Вторник', 2, 4, 10, 14, NULL, NULL, 21, 31, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(43, NULL, '2026-02-09', 'Среда', 2, 4, 8, 29, NULL, NULL, 33, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(44, NULL, '2026-02-09', 'Среда', 2, 4, 8, NULL, NULL, NULL, 48, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(45, NULL, '2026-02-09', 'Четверг', 1, 4, 31, 4, NULL, NULL, 50, 42, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(46, NULL, '2026-02-09', 'Четверг', 1, 4, NULL, NULL, 4, NULL, NULL, NULL, 17, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(48, NULL, '2026-02-02', 'Понедельник', 3, 5, 3, 4, NULL, NULL, 46, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(49, NULL, '2026-02-02', 'Вторник', 2, 5, 8, 29, NULL, NULL, 73, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(50, NULL, '2026-02-02', 'Пятница', 3, 5, 10, 14, NULL, NULL, 1, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(52, NULL, '2026-02-09', 'Понедельник', 3, 5, 3, 4, NULL, NULL, 46, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(53, NULL, '2026-02-09', 'Вторник', 2, 5, 8, 29, NULL, NULL, 73, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(54, NULL, '2026-02-09', 'Пятница', 3, 5, 10, 14, NULL, NULL, 1, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:16:29', '2026-01-22 07:16:29'),
(55, NULL, '2026-02-02', 'Понедельник', 2, 6, 14, 10, NULL, NULL, 45, 1, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(56, NULL, '2026-02-02', 'Среда', 1, 6, 29, 2, NULL, NULL, 36, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(57, NULL, '2026-02-02', 'Пятница', 2, 6, 4, 8, NULL, NULL, 28, 16, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(58, NULL, '2026-02-02', 'Пятница', 2, 6, 4, NULL, 8, NULL, 42, NULL, 51, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(59, NULL, '2026-02-09', 'Понедельник', 2, 6, 14, 10, NULL, NULL, 45, 1, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(60, NULL, '2026-02-09', 'Среда', 1, 6, 29, 2, NULL, NULL, 36, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(61, NULL, '2026-02-09', 'Пятница', 2, 6, 4, 8, NULL, NULL, 28, 16, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(62, NULL, '2026-02-09', 'Пятница', 2, 6, 4, NULL, 8, NULL, 42, NULL, 51, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(63, NULL, '2026-02-02', 'Понедельник', 3, 7, 29, 8, NULL, NULL, 36, 51, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(64, NULL, '2026-02-02', 'Понедельник', 3, 7, NULL, NULL, 8, NULL, NULL, NULL, 48, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(65, NULL, '2026-02-02', 'Четверг', 4, 7, 10, 14, NULL, NULL, 115, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(66, NULL, '2026-02-02', 'Пятница', 1, 7, 2, 4, NULL, NULL, 47, 24, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(67, NULL, '2026-02-02', 'Пятница', 1, 7, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(68, NULL, '2026-02-09', 'Понедельник', 3, 7, 29, 8, NULL, NULL, 36, 51, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(69, NULL, '2026-02-09', 'Понедельник', 3, 7, NULL, NULL, 8, NULL, NULL, NULL, 48, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(70, NULL, '2026-02-09', 'Четверг', 4, 7, 10, 14, NULL, NULL, 115, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(71, NULL, '2026-02-09', 'Пятница', 1, 7, 2, 4, NULL, NULL, 47, 24, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(72, NULL, '2026-02-09', 'Пятница', 1, 7, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(96, NULL, '2026-02-02', 'Понедельник', 4, 8, 29, 4, NULL, NULL, 36, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(97, NULL, '2026-02-02', 'Вторник', 3, 8, 2, 10, NULL, NULL, 66, 115, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(98, NULL, '2026-02-02', 'Пятница', 1, 8, 8, 14, NULL, NULL, 16, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(99, NULL, '2026-02-02', 'Пятница', 1, 8, 8, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(100, NULL, '2026-02-09', 'Понедельник', 4, 8, 29, 4, NULL, NULL, 36, 17, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(101, NULL, '2026-02-09', 'Вторник', 3, 8, 2, 10, NULL, NULL, 66, 115, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(102, NULL, '2026-02-09', 'Пятница', 1, 8, 8, 14, NULL, NULL, 16, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(103, NULL, '2026-02-09', 'Пятница', 1, 8, 8, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(104, NULL, '2026-02-02', 'Вторник', 4, 9, 14, 2, NULL, NULL, 45, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(105, NULL, '2026-02-02', 'Среда', 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(106, NULL, '2026-02-02', 'Четверг', 2, 9, 29, 8, NULL, NULL, 36, 5, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(107, NULL, '2026-02-02', 'Четверг', 2, 9, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(108, NULL, '2026-02-02', 'Пятница', 4, 9, 10, 4, NULL, NULL, 116, 28, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(109, NULL, '2026-02-02', 'Пятница', 4, 9, NULL, NULL, 4, NULL, NULL, NULL, 24, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(110, NULL, '2026-02-09', 'Вторник', 4, 9, 14, 2, NULL, NULL, 45, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(111, NULL, '2026-02-09', 'Среда', 1, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(112, NULL, '2026-02-09', 'Четверг', 2, 9, 29, 8, NULL, NULL, 36, 5, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(113, NULL, '2026-02-09', 'Четверг', 2, 9, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(114, NULL, '2026-02-09', 'Пятница', 4, 9, 10, 4, NULL, NULL, 116, 28, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(115, NULL, '2026-02-09', 'Пятница', 4, 9, NULL, NULL, 4, NULL, NULL, NULL, 24, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(116, NULL, '2026-02-02', 'Понедельник', 3, 10, 4, 10, NULL, NULL, 42, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(117, NULL, '2026-02-02', 'Понедельник', 3, 10, 4, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(118, NULL, '2026-02-02', 'Среда', 3, 10, 8, 14, NULL, NULL, 6, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(119, NULL, '2026-02-02', 'Среда', 3, 10, 8, NULL, NULL, NULL, 51, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(120, NULL, '2026-02-02', 'Четверг', 4, 10, 2, 29, NULL, NULL, 47, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(121, NULL, '2026-02-09', 'Понедельник', 3, 10, 4, 10, NULL, NULL, 42, 21, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(122, NULL, '2026-02-09', 'Понедельник', 3, 10, 4, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(123, NULL, '2026-02-09', 'Среда', 3, 10, 8, 14, NULL, NULL, 6, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(124, NULL, '2026-02-09', 'Среда', 3, 10, 8, NULL, NULL, NULL, 51, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(125, NULL, '2026-02-09', 'Четверг', 4, 10, 2, 29, NULL, NULL, 47, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(126, NULL, '2026-02-02', 'Вторник', 2, 11, 8, 14, NULL, NULL, 6, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(127, NULL, '2026-02-02', 'Вторник', 2, 11, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(128, NULL, '2026-02-02', 'Среда', 4, 11, 4, 2, NULL, NULL, 24, 66, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(129, NULL, '2026-02-02', 'Среда', 4, 11, 4, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(130, NULL, '2026-02-02', 'Четверг', 1, 11, 29, 10, NULL, NULL, 36, 115, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(131, NULL, '2026-02-09', 'Вторник', 2, 11, 8, 14, NULL, NULL, 6, 45, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(132, NULL, '2026-02-09', 'Вторник', 2, 11, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(133, NULL, '2026-02-09', 'Среда', 4, 11, 4, 2, NULL, NULL, 24, 66, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(134, NULL, '2026-02-09', 'Среда', 4, 11, 4, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(135, NULL, '2026-02-09', 'Четверг', 1, 11, 29, 10, NULL, NULL, 36, 115, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(136, NULL, '2026-02-02', 'Вторник', 1, 14, 1, 11, NULL, NULL, 47, 60, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(137, NULL, '2026-02-02', 'Среда', 3, 14, 15, 14, NULL, NULL, 68, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(138, NULL, '2026-02-02', 'Пятница', 4, 14, 6, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(139, NULL, '2026-02-02', 'Пятница', 4, 14, 6, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(140, NULL, '2026-02-09', 'Вторник', 1, 14, 1, 11, NULL, NULL, 47, 60, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(141, NULL, '2026-02-09', 'Среда', 3, 14, 15, 14, NULL, NULL, 68, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(142, NULL, '2026-02-09', 'Пятница', 4, 14, 6, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(143, NULL, '2026-02-09', 'Пятница', 4, 14, 6, NULL, NULL, NULL, 37, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(144, NULL, '2026-02-02', 'Понедельник', 1, 15, 6, 1, NULL, NULL, 45, 117, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(145, NULL, '2026-02-02', 'Понедельник', 1, 15, 6, NULL, NULL, NULL, 67, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(146, NULL, '2026-02-02', 'Понедельник', 2, 15, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(147, NULL, '2026-02-02', 'Среда', 3, 15, 29, 11, NULL, NULL, 36, 60, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(148, NULL, '2026-02-02', 'Пятница', 1, 15, 14, 15, NULL, NULL, 45, 53, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(149, NULL, '2026-02-09', 'Понедельник', 1, 15, 6, 1, NULL, NULL, 45, 117, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(150, NULL, '2026-02-09', 'Понедельник', 1, 15, 6, NULL, NULL, NULL, 67, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(151, NULL, '2026-02-09', 'Понедельник', 2, 15, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(152, NULL, '2026-02-09', 'Среда', 3, 15, 29, 11, NULL, NULL, 36, 60, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(153, NULL, '2026-02-09', 'Пятница', 1, 15, 14, 15, NULL, NULL, 45, 53, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(154, NULL, '2026-02-02', 'Понедельник', 1, 16, 31, 7, NULL, NULL, 44, 68, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(155, NULL, '2026-02-02', 'Четверг', 4, 16, 4, 29, NULL, NULL, 24, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(156, NULL, '2026-02-02', 'Четверг', 4, 16, 4, NULL, NULL, NULL, 42, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(157, NULL, '2026-02-02', 'Пятница', 2, 16, 8, 14, NULL, NULL, 33, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(158, NULL, '2026-02-02', 'Пятница', 2, 16, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(159, NULL, '2026-02-09', 'Понедельник', 1, 16, 31, 7, NULL, NULL, 44, 68, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(160, NULL, '2026-02-09', 'Четверг', 4, 16, 4, 29, NULL, NULL, 24, 71, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(161, NULL, '2026-02-09', 'Четверг', 4, 16, 4, NULL, NULL, NULL, 42, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(162, NULL, '2026-02-09', 'Пятница', 2, 16, 8, 14, NULL, NULL, 33, 61, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(163, NULL, '2026-02-09', 'Пятница', 2, 16, 8, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(164, NULL, '2026-02-02', 'Понедельник', 4, 17, 4, 7, NULL, NULL, 24, 68, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(165, NULL, '2026-02-02', 'Понедельник', 4, 17, 4, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(166, NULL, '2026-02-02', 'Вторник', 1, 17, 14, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(167, NULL, '2026-02-02', 'Пятница', 2, 17, 8, 2, NULL, NULL, 6, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(168, NULL, '2026-02-02', 'Пятница', 2, 17, 8, NULL, NULL, NULL, 48, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(169, NULL, '2026-02-09', 'Понедельник', 4, 17, 4, 7, NULL, NULL, 24, 68, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(170, NULL, '2026-02-09', 'Понедельник', 4, 17, 4, NULL, NULL, NULL, 28, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(171, NULL, '2026-02-09', 'Вторник', 1, 17, 14, 29, NULL, NULL, 45, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(172, NULL, '2026-02-09', 'Пятница', 2, 17, 8, 2, NULL, NULL, 6, 47, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(173, NULL, '2026-02-09', 'Пятница', 2, 17, 8, NULL, NULL, NULL, 48, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(174, NULL, '2026-02-02', 'Вторник', 3, 18, 14, 4, NULL, NULL, 45, 24, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(175, NULL, '2026-02-02', 'Вторник', 3, 18, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(176, NULL, '2026-02-02', 'Среда', 3, 18, 7, 29, NULL, NULL, 53, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(177, NULL, '2026-02-02', 'Четверг', 4, 18, 8, 2, NULL, NULL, 73, 9, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(178, NULL, '2026-02-02', 'Четверг', 4, 18, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(179, NULL, '2026-02-09', 'Вторник', 3, 18, 14, 4, NULL, NULL, 45, 24, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(180, NULL, '2026-02-09', 'Вторник', 3, 18, NULL, NULL, 4, NULL, NULL, NULL, 28, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(181, NULL, '2026-02-09', 'Среда', 3, 18, 7, 29, NULL, NULL, 53, 36, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(182, NULL, '2026-02-09', 'Четверг', 4, 18, 8, 2, NULL, NULL, 73, 9, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '1', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(183, NULL, '2026-02-09', 'Четверг', 4, 18, 8, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '2', 0, '2026-01-23 07:13:55', '2026-01-23 07:13:55');

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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `group_type` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_subjects`
--

INSERT INTO `first_course_subjects` (`id`, `module_title`, `module_index`, `subject_name`, `name_ru`, `name_kz`, `created_at`, `updated_at`, `group_type`) VALUES
(1, 'ООД 1', 1, 'Русский язык', 'Русский язык', 'Орыс тілі', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'ru'),
(2, 'ООД 2', 2, 'Русская литература', 'Русская литература', 'Орыс әдебиеті', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'ru'),
(3, 'ООД 3', 3, 'Казахский язык и литература', 'Казахский язык и литература', 'Қазақ тілі мен әдебиеті', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'ru'),
(4, 'ООД 4', 4, 'Иностранный язык', 'Иностранный язык', 'Шетел тілі', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(5, 'ООД 5', 5, 'Математика', 'Математика', 'Математика', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(6, 'ООД 6', 6, 'Информатика', 'Информатика', 'Информатика', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(7, 'ООД 7', 7, 'История Казахстана', 'История Казахстана', 'Қазақстан тарихы', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(8, 'ООД 8', 8, 'Физическая культура', 'Физическая культура', 'Дене тәрбиесі', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(9, 'ООД 9', 9, 'НВТП', 'НВТП', 'НВТП', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'hidden'),
(10, 'ООД 10', 10, 'Физика', 'Физика', 'Физика', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(11, 'ООД 11', 11, 'Химия', 'Химия', 'Химия', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(12, 'ООД 12', 12, 'Биология', 'Биология', 'Биология', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(13, 'ООД 13', 13, 'География', 'География', 'География', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(14, 'ООД 14', 14, 'Графика и проектирование', 'Графика и проектирование', 'Графика және жобалау', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(15, 'ООД 15', 15, 'Всемирная история', 'Всемирная история', 'Дүние жүзі тарихы', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'both'),
(16, 'ООД 16', 16, 'Глобальные компетенции', 'Глобальные компетенции', 'Ғаламдық құзыреттер', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'ru'),
(26, 'ООД 26', 26, 'НВиТП', 'НВиТП', 'НВиТП', '2025-11-23 14:15:39', '2026-01-16 07:28:42', 'hidden'),
(29, NULL, NULL, 'Начальная военная и технологическая подготовка', 'Начальная военная и технологическая подготовка', 'Бастапқы әскери және технологиялық дайындық', '2026-01-16 07:28:42', '2026-01-16 07:28:42', 'both'),
(30, NULL, NULL, 'Қазақ тілі', NULL, 'Қазақ тілі', '2026-01-16 07:28:42', '2026-01-16 07:28:42', 'kz'),
(31, NULL, NULL, 'Қазақ әдебиеті', NULL, 'Қазақ әдебиеті', '2026-01-16 07:28:42', '2026-01-16 07:28:42', 'kz'),
(32, NULL, NULL, 'Орыс тілі және әдәбиеті', NULL, 'Орыс тілі және әдәбиеті', '2026-01-16 07:28:42', '2026-01-16 07:28:42', 'kz');

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_teacher_subjects`
--

CREATE TABLE `first_course_teacher_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `first_course_teacher_subjects`
--

INSERT INTO `first_course_teacher_subjects` (`id`, `teacher_id`, `subject_id`, `created_at`, `updated_at`) VALUES
(1, 9, 1, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(2, 9, 2, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(3, 9, 32, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(4, 8, 30, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(5, 8, 3, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(6, 8, 32, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(7, 47, 1, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(8, 47, 2, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(9, 46, 3, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(10, 46, 30, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(11, 44, 3, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(12, 44, 31, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(13, 57, 30, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(14, 57, 31, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(15, 57, 3, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(16, 50, 31, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(17, 50, 3, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(18, 66, 1, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(19, 66, 2, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(20, 66, 32, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(21, 17, 4, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(22, 28, 4, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(23, 42, 4, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(24, 24, 4, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(25, 40, 5, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(26, 55, 5, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(27, 27, 5, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(28, 21, 5, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(29, 21, 10, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(30, 45, 6, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(31, 45, 14, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(32, 61, 6, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(33, 61, 14, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(34, 37, 6, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(35, 31, 6, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(36, 31, 14, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(37, 67, 6, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(38, 53, 7, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(39, 53, 15, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(40, 53, 13, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(41, 68, 7, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(42, 68, 15, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(43, 35, 13, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(44, 35, 7, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(45, 35, 15, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(46, 56, 13, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(47, 56, 15, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(48, 1, 10, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(49, 69, 10, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(50, 60, 11, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(51, 43, 11, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(52, 43, 12, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(53, 12, 12, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(54, 48, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(55, 6, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(56, 70, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(57, 33, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(58, 16, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(59, 51, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(60, 5, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(61, 4, 8, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(62, 36, 29, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(63, 71, 29, '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(69, 72, 8, '2026-01-22 02:42:57', '2026-01-22 02:42:57'),
(70, 73, 8, '2026-01-22 07:13:38', '2026-01-22 07:13:38'),
(71, 115, 10, '2026-01-23 02:15:51', '2026-01-23 02:15:51'),
(72, 116, 10, '2026-01-23 03:31:32', '2026-01-23 03:31:32'),
(73, 117, 1, '2026-01-23 05:34:57', '2026-01-23 05:34:57');

-- --------------------------------------------------------

--
-- Структура таблицы `form_two_normatives`
--

CREATE TABLE `form_two_normatives` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` smallint UNSIGNED NOT NULL DEFAULT '0',
  `hours_per_class` tinyint UNSIGNED NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `form_two_normatives`
--

INSERT INTO `form_two_normatives` (`id`, `group_id`, `subject_id`, `teacher_id`, `month`, `year`, `total_hours`, `hours_per_class`, `created_at`, `updated_at`) VALUES
(39, 1, 1, NULL, 2, 2026, 34, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(40, 1, 2, NULL, 2, 2026, 52, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(43, 1, 5, NULL, 2, 2026, 40, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(45, 1, 7, NULL, 2, 2026, 38, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(47, 1, 29, NULL, 2, 2026, 48, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(48, 1, 10, NULL, 2, 2026, 52, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(49, 1, 11, NULL, 2, 2026, 34, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(50, 1, 12, NULL, 2, 2026, 32, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(51, 1, 13, NULL, 2, 2026, 32, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(52, 1, 14, NULL, 2, 2026, 52, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(53, 1, 15, NULL, 2, 2026, 32, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(54, 1, 16, NULL, 2, 2026, 24, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(55, 1, 3, NULL, 2, 2026, 34, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(56, 1, 4, NULL, 2, 2026, 52, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(57, 1, 6, NULL, 2, 2026, 32, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(58, 1, 8, NULL, 2, 2026, 60, 2, '2026-01-19 04:27:13', '2026-01-19 04:27:13'),
(79, 3, 30, NULL, 2, 2026, 34, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(80, 3, 31, NULL, 2, 2026, 52, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(83, 3, 5, NULL, 2, 2026, 40, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(85, 3, 7, NULL, 2, 2026, 38, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(87, 3, 29, NULL, 2, 2026, 48, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(88, 3, 10, NULL, 2, 2026, 52, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(89, 3, 11, NULL, 2, 2026, 34, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(90, 3, 12, NULL, 2, 2026, 32, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(91, 3, 13, NULL, 2, 2026, 32, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(92, 3, 14, NULL, 2, 2026, 52, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(93, 3, 15, NULL, 2, 2026, 32, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(94, 3, 16, NULL, 2, 2026, 24, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(95, 3, 32, NULL, 2, 2026, 34, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(96, 3, 4, NULL, 2, 2026, 52, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(97, 3, 6, NULL, 2, 2026, 32, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(98, 3, 8, NULL, 2, 2026, 60, 2, '2026-01-19 04:59:52', '2026-01-19 04:59:52'),
(119, 2, 30, NULL, 2, 2026, 34, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(120, 2, 31, NULL, 2, 2026, 52, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(123, 2, 5, NULL, 2, 2026, 40, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(125, 2, 7, NULL, 2, 2026, 38, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(127, 2, 29, NULL, 2, 2026, 48, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(128, 2, 10, NULL, 2, 2026, 52, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(129, 2, 11, NULL, 2, 2026, 34, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(130, 2, 12, NULL, 2, 2026, 32, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(131, 2, 13, NULL, 2, 2026, 32, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(132, 2, 14, NULL, 2, 2026, 52, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(133, 2, 15, NULL, 2, 2026, 32, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(134, 2, 16, NULL, 2, 2026, 24, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(135, 2, 32, NULL, 2, 2026, 34, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(136, 2, 4, NULL, 2, 2026, 52, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(137, 2, 6, NULL, 2, 2026, 32, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(138, 2, 8, NULL, 2, 2026, 60, 2, '2026-01-19 05:05:19', '2026-01-19 05:05:19'),
(199, 4, 30, NULL, 2, 2026, 34, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(200, 4, 31, NULL, 2, 2026, 52, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(203, 4, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(205, 4, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(207, 4, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(208, 4, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(209, 4, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(210, 4, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(211, 4, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(212, 4, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(213, 4, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(214, 4, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(215, 4, 32, NULL, 2, 2026, 34, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(216, 4, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(217, 4, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(218, 4, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:10:22', '2026-01-20 03:10:22'),
(315, 5, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(316, 5, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(317, 5, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(318, 5, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(319, 5, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(320, 5, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(321, 5, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(322, 5, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(323, 5, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(324, 5, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(325, 5, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(326, 5, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(327, 5, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(328, 5, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(329, 5, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(330, 5, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:27:28', '2026-01-20 03:27:28'),
(347, 6, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(348, 6, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(349, 6, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(350, 6, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(351, 6, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(352, 6, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(353, 6, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(354, 6, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(355, 6, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(356, 6, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(357, 6, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(358, 6, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(359, 6, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(360, 6, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(361, 6, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(362, 6, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:42:13', '2026-01-20 03:42:13'),
(379, 7, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(380, 7, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(381, 7, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(382, 7, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(383, 7, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(384, 7, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(385, 7, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(386, 7, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(387, 7, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(388, 7, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(389, 7, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(390, 7, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(391, 7, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(392, 7, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(393, 7, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(394, 7, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:45:37', '2026-01-20 03:45:37'),
(411, 8, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(412, 8, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(413, 8, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(414, 8, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(415, 8, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(416, 8, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(417, 8, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(418, 8, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(419, 8, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(420, 8, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(421, 8, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(422, 8, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(423, 8, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(424, 8, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(425, 8, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(426, 8, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:48:33', '2026-01-20 03:48:33'),
(443, 9, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(444, 9, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(445, 9, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(446, 9, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(447, 9, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(448, 9, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(449, 9, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(450, 9, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(451, 9, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(452, 9, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(453, 9, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(454, 9, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(455, 9, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(456, 9, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(457, 9, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(458, 9, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:52:13', '2026-01-20 03:52:13'),
(475, 10, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(476, 10, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(477, 10, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(478, 10, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(479, 10, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(480, 10, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(481, 10, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(482, 10, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(483, 10, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(484, 10, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(485, 10, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(486, 10, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(487, 10, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(488, 10, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(489, 10, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(490, 10, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:54:33', '2026-01-20 03:54:33'),
(491, 11, 1, NULL, 2, 2026, 34, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(492, 11, 2, NULL, 2, 2026, 52, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(493, 11, 3, NULL, 2, 2026, 34, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(494, 11, 4, NULL, 2, 2026, 52, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(495, 11, 5, NULL, 2, 2026, 40, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(496, 11, 6, NULL, 2, 2026, 32, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(497, 11, 7, NULL, 2, 2026, 38, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(498, 11, 8, NULL, 2, 2026, 60, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(499, 11, 29, NULL, 2, 2026, 48, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(500, 11, 10, NULL, 2, 2026, 52, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(501, 11, 11, NULL, 2, 2026, 34, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(502, 11, 12, NULL, 2, 2026, 32, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(503, 11, 13, NULL, 2, 2026, 32, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(504, 11, 14, NULL, 2, 2026, 52, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(505, 11, 15, NULL, 2, 2026, 32, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(506, 11, 16, NULL, 2, 2026, 24, 2, '2026-01-20 03:57:11', '2026-01-20 03:57:11'),
(507, 12, 30, NULL, 2, 2026, 60, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(508, 12, 31, NULL, 2, 2026, 36, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(509, 12, 32, NULL, 2, 2026, 36, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(510, 12, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(511, 12, 5, NULL, 2, 2026, 60, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(512, 12, 6, NULL, 2, 2026, 12, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(513, 12, 7, NULL, 2, 2026, 40, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(514, 12, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(515, 12, 29, NULL, 2, 2026, 56, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(516, 12, 10, NULL, 2, 2026, 36, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(517, 12, 11, NULL, 2, 2026, 56, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(518, 12, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(519, 12, 13, NULL, 2, 2026, 40, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(520, 12, 14, NULL, 2, 2026, 52, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(521, 12, 15, NULL, 2, 2026, 12, 2, '2026-01-20 04:00:42', '2026-01-20 04:00:42'),
(537, 13, 30, NULL, 2, 2026, 60, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(538, 13, 31, NULL, 2, 2026, 36, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(539, 13, 32, NULL, 2, 2026, 36, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(540, 13, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(541, 13, 5, NULL, 2, 2026, 60, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(542, 13, 6, NULL, 2, 2026, 12, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(543, 13, 7, NULL, 2, 2026, 40, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(544, 13, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(545, 13, 29, NULL, 2, 2026, 56, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(546, 13, 10, NULL, 2, 2026, 36, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(547, 13, 11, NULL, 2, 2026, 56, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(548, 13, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(549, 13, 13, NULL, 2, 2026, 40, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(550, 13, 14, NULL, 2, 2026, 52, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(551, 13, 15, NULL, 2, 2026, 12, 2, '2026-01-20 04:06:14', '2026-01-20 04:06:14'),
(552, 14, 1, NULL, 2, 2026, 60, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(553, 14, 2, NULL, 2, 2026, 36, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(554, 14, 3, NULL, 2, 2026, 36, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(555, 14, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(556, 14, 5, NULL, 2, 2026, 60, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(557, 14, 6, NULL, 2, 2026, 12, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(558, 14, 7, NULL, 2, 2026, 40, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(559, 14, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(560, 14, 29, NULL, 2, 2026, 56, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(561, 14, 10, NULL, 2, 2026, 36, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(562, 14, 11, NULL, 2, 2026, 56, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(563, 14, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(564, 14, 13, NULL, 2, 2026, 40, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(565, 14, 14, NULL, 2, 2026, 52, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(566, 14, 15, NULL, 2, 2026, 12, 2, '2026-01-20 04:09:13', '2026-01-20 04:09:13'),
(582, 15, 1, NULL, 2, 2026, 66, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(583, 15, 2, NULL, 2, 2026, 36, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(584, 15, 3, NULL, 2, 2026, 36, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(585, 15, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(586, 15, 5, NULL, 2, 2026, 60, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(587, 15, 6, NULL, 2, 2026, 12, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(588, 15, 7, NULL, 2, 2026, 40, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(589, 15, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(590, 15, 29, NULL, 2, 2026, 56, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(591, 15, 10, NULL, 2, 2026, 36, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(592, 15, 11, NULL, 2, 2026, 56, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(593, 15, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(594, 15, 13, NULL, 2, 2026, 40, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(595, 15, 14, NULL, 2, 2026, 52, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(596, 15, 15, NULL, 2, 2026, 12, 2, '2026-01-20 04:12:37', '2026-01-20 04:12:37'),
(597, 16, 30, NULL, 2, 2026, 36, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(598, 16, 31, NULL, 2, 2026, 52, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(599, 16, 32, NULL, 2, 2026, 36, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(600, 16, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(601, 16, 5, NULL, 2, 2026, 64, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(602, 16, 6, NULL, 2, 2026, 28, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(603, 16, 7, NULL, 2, 2026, 52, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(604, 16, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(605, 16, 29, NULL, 2, 2026, 48, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(606, 16, 10, NULL, 2, 2026, 32, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(607, 16, 11, NULL, 2, 2026, 32, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(608, 16, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(609, 16, 13, NULL, 2, 2026, 36, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(610, 16, 14, NULL, 2, 2026, 48, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(611, 16, 15, NULL, 2, 2026, 32, 2, '2026-01-20 04:15:23', '2026-01-20 04:15:23'),
(612, 17, 1, NULL, 2, 2026, 36, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(613, 17, 2, NULL, 2, 2026, 52, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(614, 17, 3, NULL, 2, 2026, 36, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(615, 17, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(616, 17, 5, NULL, 2, 2026, 64, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(617, 17, 6, NULL, 2, 2026, 28, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(618, 17, 7, NULL, 2, 2026, 52, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(619, 17, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(620, 17, 29, NULL, 2, 2026, 48, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(621, 17, 10, NULL, 2, 2026, 32, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(622, 17, 11, NULL, 2, 2026, 32, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(623, 17, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(624, 17, 13, NULL, 2, 2026, 36, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(625, 17, 14, NULL, 2, 2026, 48, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(626, 17, 15, NULL, 2, 2026, 32, 2, '2026-01-20 04:19:32', '2026-01-20 04:19:32'),
(642, 18, 30, NULL, 2, 2026, 36, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(643, 18, 31, NULL, 2, 2026, 52, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(644, 18, 32, NULL, 2, 2026, 36, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(645, 18, 4, NULL, 2, 2026, 60, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(646, 18, 5, NULL, 2, 2026, 64, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(647, 18, 6, NULL, 2, 2026, 28, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(648, 18, 7, NULL, 2, 2026, 52, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(649, 18, 8, NULL, 2, 2026, 60, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(650, 18, 29, NULL, 2, 2026, 48, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(651, 18, 10, NULL, 2, 2026, 32, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(652, 18, 11, NULL, 2, 2026, 32, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(653, 18, 12, NULL, 2, 2026, 32, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(654, 18, 13, NULL, 2, 2026, 36, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(655, 18, 14, NULL, 2, 2026, 48, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05'),
(656, 18, 15, NULL, 2, 2026, 32, 2, '2026-01-20 04:23:05', '2026-01-20 04:23:05');

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
(52, 1, 2, 2026, '2026-02-02', 2, 2, 2, 9, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(53, 1, 2, 2026, '2026-02-04', 4, 4, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(54, 1, 2, 2026, '2026-02-06', 1, 6, 10, 1, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(55, 1, 2, 2026, '2026-02-09', 2, 9, 4, 17, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(56, 1, 2, 2026, '2026-02-09', 2, 9, 4, 28, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(57, 1, 2, 2026, '2026-02-11', 4, 11, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(58, 1, 2, 2026, '2026-02-13', 1, 13, 16, 65, 1, 24, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:05:54', '2026-01-22 02:05:54'),
(65, 2, 2, 2026, '2026-02-02', 1, 2, 31, 57, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(66, 2, 2, 2026, '2026-02-03', 3, 3, 29, 71, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(67, 2, 2, 2026, '2026-02-05', 3, 5, 4, 42, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(68, 2, 2, 2026, '2026-02-05', 3, 5, 4, 24, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(69, 2, 2, 2026, '2026-02-09', 1, 9, 10, 21, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(70, 2, 2, 2026, '2026-02-10', 3, 10, 14, 61, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(71, 2, 2, 2026, '2026-02-12', 3, 12, 8, 72, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(72, 2, 2, 2026, '2026-02-12', 3, 12, 8, 70, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 02:46:33', '2026-01-22 02:46:33'),
(73, 3, 2, 2026, '2026-02-03', 2, 3, 31, 50, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(74, 3, 2, 2026, '2026-02-04', 1, 4, 8, 33, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(75, 3, 2, 2026, '2026-02-04', 1, 4, 8, 70, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(76, 3, 2, 2026, '2026-02-05', 2, 5, 29, 71, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(77, 3, 2, 2026, '2026-02-10', 2, 10, 4, 42, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(78, 3, 2, 2026, '2026-02-10', 2, 10, 4, 17, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(79, 3, 2, 2026, '2026-02-11', 1, 11, 10, 21, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(80, 3, 2, 2026, '2026-02-11', 1, 11, 8, 70, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(81, 3, 2, 2026, '2026-02-12', 2, 12, 14, 61, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 03:01:53', '2026-01-22 03:01:53'),
(82, 4, 2, 2026, '2026-02-03', 2, 3, 10, 21, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(83, 4, 2, 2026, '2026-02-04', 2, 4, 8, 33, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(84, 4, 2, 2026, '2026-02-04', 2, 4, 8, 48, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(85, 4, 2, 2026, '2026-02-05', 1, 5, 31, 50, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(86, 4, 2, 2026, '2026-02-10', 2, 10, 14, 31, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(87, 4, 2, 2026, '2026-02-11', 2, 11, 29, 71, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(88, 4, 2, 2026, '2026-02-11', 2, 11, 8, 48, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'single', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(89, 4, 2, 2026, '2026-02-12', 1, 12, 4, 42, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(90, 4, 2, 2026, '2026-02-12', 1, 12, 4, 17, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 06:50:02', '2026-01-22 06:50:02'),
(99, 6, 2, 2026, '2026-02-02', 2, 2, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(100, 6, 2, 2026, '2026-02-04', 1, 4, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(101, 6, 2, 2026, '2026-02-06', 2, 6, 4, 28, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(102, 6, 2, 2026, '2026-02-06', 2, 6, 4, 42, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(103, 6, 2, 2026, '2026-02-09', 2, 9, 10, 1, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(104, 6, 2, 2026, '2026-02-11', 1, 11, 2, 47, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(105, 6, 2, 2026, '2026-02-13', 2, 13, 8, 16, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(106, 6, 2, 2026, '2026-02-13', 2, 13, 8, 51, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-22 07:43:04', '2026-01-22 07:43:04'),
(107, 7, 2, 2026, '2026-02-02', 3, 2, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(108, 7, 2, 2026, '2026-02-05', 4, 5, 10, 115, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(109, 7, 2, 2026, '2026-02-06', 1, 6, 2, 47, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(110, 7, 2, 2026, '2026-02-09', 3, 9, 8, 51, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(111, 7, 2, 2026, '2026-02-09', 3, 9, 8, 48, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(112, 7, 2, 2026, '2026-02-12', 4, 12, 14, 61, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(113, 7, 2, 2026, '2026-02-13', 1, 13, 4, 24, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(114, 7, 2, 2026, '2026-02-13', 1, 13, 4, 28, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 03:24:32', '2026-01-23 03:24:32'),
(155, 8, 2, 2026, '2026-02-02', 4, 2, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(156, 8, 2, 2026, '2026-02-03', 3, 3, 2, 66, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(157, 8, 2, 2026, '2026-02-06', 1, 6, 8, 16, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(158, 8, 2, 2026, '2026-02-06', 1, 6, 8, 4, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(159, 8, 2, 2026, '2026-02-09', 4, 9, 4, 17, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(160, 8, 2, 2026, '2026-02-10', 3, 10, 10, 115, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(161, 8, 2, 2026, '2026-02-13', 1, 13, 14, 61, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(162, 8, 2, 2026, '2026-02-13', 1, 13, 14, 61, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:02:21', '2026-01-23 04:02:21'),
(169, 5, 2, 2026, '2026-02-09', 3, 9, 4, 17, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(170, 5, 2, 2026, '2026-02-10', 2, 10, 29, 71, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(171, 5, 2, 2026, '2026-02-13', 3, 13, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(172, 5, 2, 2026, '2026-02-02', 3, 2, 3, 46, 1, 34, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(173, 5, 2, 2026, '2026-02-03', 2, 3, 8, 73, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(174, 5, 2, 2026, '2026-02-06', 3, 6, 10, 1, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:06:51', '2026-01-23 04:06:51'),
(175, 9, 2, 2026, '2026-02-03', 4, 3, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(176, 9, 2, 2026, '2026-02-05', 2, 5, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(177, 9, 2, 2026, '2026-02-05', 2, 5, 8, 33, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(178, 9, 2, 2026, '2026-02-06', 4, 6, 10, 116, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(179, 9, 2, 2026, '2026-02-10', 4, 10, 2, 47, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(180, 9, 2, 2026, '2026-02-12', 2, 12, 8, 5, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(181, 9, 2, 2026, '2026-02-12', 2, 12, 8, 5, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(182, 9, 2, 2026, '2026-02-13', 4, 13, 4, 28, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(183, 9, 2, 2026, '2026-02-13', 4, 13, 4, 24, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:16:31', '2026-01-23 04:16:31'),
(184, 10, 2, 2026, '2026-02-02', 3, 2, 4, 42, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(185, 10, 2, 2026, '2026-02-02', 3, 2, 4, 28, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(186, 10, 2, 2026, '2026-02-04', 3, 4, 8, 6, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(187, 10, 2, 2026, '2026-02-04', 3, 4, 8, 51, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(188, 10, 2, 2026, '2026-02-05', 4, 5, 2, 47, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(189, 10, 2, 2026, '2026-02-09', 3, 9, 10, 21, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(190, 10, 2, 2026, '2026-02-09', 3, 9, 10, 21, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(191, 10, 2, 2026, '2026-02-11', 3, 11, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(192, 10, 2, 2026, '2026-02-11', 3, 11, 14, 45, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(193, 10, 2, 2026, '2026-02-12', 4, 12, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 04:28:22', '2026-01-23 04:28:22'),
(194, 11, 2, 2026, '2026-02-03', 2, 3, 8, 6, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(195, 11, 2, 2026, '2026-02-03', 2, 3, 8, 33, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(196, 11, 2, 2026, '2026-02-04', 4, 4, 4, 24, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(197, 11, 2, 2026, '2026-02-04', 4, 4, 4, 17, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(198, 11, 2, 2026, '2026-02-05', 1, 5, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(199, 11, 2, 2026, '2026-02-10', 2, 10, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(200, 11, 2, 2026, '2026-02-10', 2, 10, 14, 45, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(201, 11, 2, 2026, '2026-02-11', 4, 11, 2, 66, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(202, 11, 2, 2026, '2026-02-11', 4, 11, 2, 66, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(203, 11, 2, 2026, '2026-02-12', 1, 12, 10, 115, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:13:22', '2026-01-23 05:13:22'),
(204, 14, 2, 2026, '2026-02-03', 1, 3, 1, 47, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(205, 14, 2, 2026, '2026-02-04', 3, 4, 15, 68, 1, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(206, 14, 2, 2026, '2026-02-06', 4, 6, 6, 45, 1, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(207, 14, 2, 2026, '2026-02-06', 4, 6, 6, 37, 2, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(208, 14, 2, 2026, '2026-02-10', 1, 10, 11, 60, 1, 56, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(209, 14, 2, 2026, '2026-02-11', 3, 11, 14, 61, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(210, 14, 2, 2026, '2026-02-13', 4, 13, 29, 36, 1, 56, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(211, 14, 2, 2026, '2026-02-13', 4, 13, 29, 36, 2, 56, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:26:41', '2026-01-23 05:26:41'),
(212, 15, 2, 2026, '2026-02-02', 1, 2, 6, 45, 1, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(213, 15, 2, 2026, '2026-02-02', 1, 2, 6, 67, 2, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(214, 15, 2, 2026, '2026-02-04', 3, 4, 29, 36, 1, 56, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(215, 15, 2, 2026, '2026-02-06', 1, 6, 14, 45, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(216, 15, 2, 2026, '2026-02-09', 1, 9, 1, 117, 1, 66, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(217, 15, 2, 2026, '2026-02-09', 1, 9, 1, 117, 2, 66, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(218, 15, 2, 2026, '2026-02-09', 2, 9, 1, NULL, 1, 66, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(219, 15, 2, 2026, '2026-02-11', 3, 11, 11, 60, 1, 56, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(220, 15, 2, 2026, '2026-02-13', 1, 13, 15, 53, 1, 12, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 05:43:15', '2026-01-23 05:43:15'),
(221, 16, 2, 2026, '2026-02-02', 1, 2, 31, 44, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(222, 16, 2, 2026, '2026-02-05', 4, 5, 4, 24, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(223, 16, 2, 2026, '2026-02-05', 4, 5, 4, 42, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(224, 16, 2, 2026, '2026-02-06', 2, 6, 8, 33, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(225, 16, 2, 2026, '2026-02-06', 2, 6, 8, 5, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(226, 16, 2, 2026, '2026-02-09', 1, 9, 7, 68, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(227, 16, 2, 2026, '2026-02-12', 4, 12, 29, 71, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(228, 16, 2, 2026, '2026-02-12', 4, 12, 29, 71, 2, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(229, 16, 2, 2026, '2026-02-13', 2, 13, 14, 61, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(230, 16, 2, 2026, '2026-02-13', 2, 13, 14, 61, 2, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:36:30', '2026-01-23 06:36:30'),
(231, 17, 2, 2026, '2026-02-02', 4, 2, 4, 24, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(232, 17, 2, 2026, '2026-02-02', 4, 2, 4, 28, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(233, 17, 2, 2026, '2026-02-03', 1, 3, 14, 45, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(234, 17, 2, 2026, '2026-02-06', 2, 6, 8, 6, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(235, 17, 2, 2026, '2026-02-06', 2, 6, 8, 48, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(236, 17, 2, 2026, '2026-02-09', 4, 9, 7, 68, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(237, 17, 2, 2026, '2026-02-09', 4, 9, 7, 68, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(238, 17, 2, 2026, '2026-02-10', 1, 10, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(239, 17, 2, 2026, '2026-02-13', 2, 13, 2, 47, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(240, 17, 2, 2026, '2026-02-13', 2, 13, 2, 47, 2, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 06:50:20', '2026-01-23 06:50:20'),
(241, 18, 2, 2026, '2026-02-03', 3, 3, 14, 45, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(242, 18, 2, 2026, '2026-02-04', 3, 4, 7, 53, 1, 52, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(243, 18, 2, 2026, '2026-02-05', 4, 5, 8, 73, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(244, 18, 2, 2026, '2026-02-05', 4, 5, 8, 33, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'numerator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(245, 18, 2, 2026, '2026-02-10', 3, 10, 4, 24, 1, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(246, 18, 2, 2026, '2026-02-10', 3, 10, 4, 28, 2, 60, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(247, 18, 2, 2026, '2026-02-11', 3, 11, 29, 36, 1, 48, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(248, 18, 2, 2026, '2026-02-12', 4, 12, 2, 9, 1, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 07:13:55', '2026-01-23 07:13:55'),
(249, 18, 2, 2026, '2026-02-12', 4, 12, 2, 9, 2, 0, 2, 'normal', NULL, NULL, NULL, 2, NULL, NULL, 'denominator', '2026-01-23 07:13:55', '2026-01-23 07:13:55');

-- --------------------------------------------------------

--
-- Структура таблицы `fourth_course_group`
--

CREATE TABLE `fourth_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_subgroups` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_type` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `fourth_course_group`
--

INSERT INTO `fourth_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `has_subgroups`, `created_at`, `updated_at`, `group_type`) VALUES
(1, 'ТЭ-422', 422, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru');

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
-- Структура таблицы `fourth_course_teacher_subjects`
--

CREATE TABLE `fourth_course_teacher_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
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
-- Структура таблицы `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_month` tinyint UNSIGNED NOT NULL,
  `start_day` tinyint UNSIGNED NOT NULL,
  `end_month` tinyint UNSIGNED NOT NULL,
  `end_day` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `holidays`
--

INSERT INTO `holidays` (`id`, `name`, `start_month`, `start_day`, `end_month`, `end_day`, `year`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Новый год', 1, 1, 1, 1, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(2, 'Новый год', 1, 2, 1, 2, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(3, 'Новый год', 1, 3, 1, 3, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(4, 'Новый год', 1, 4, 1, 4, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(5, 'Международный женский день', 3, 8, 3, 8, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(6, 'Наурыз', 3, 21, 3, 21, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(7, 'Наурыз', 3, 22, 3, 22, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(8, 'Наурыз', 3, 23, 3, 23, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(9, 'День единства народа Казахстана', 5, 1, 5, 1, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(10, 'День защитника Отечества', 5, 7, 5, 7, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(11, 'День Победы', 5, 9, 5, 9, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(12, 'День столицы', 7, 6, 7, 6, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(13, 'День Конституции', 8, 30, 8, 30, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(14, 'День Республики', 10, 25, 10, 25, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(15, 'День Первого Президента', 12, 1, 12, 1, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(16, 'День Независимости', 12, 16, 12, 16, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46'),
(17, 'Каникулы', 1, 19, 2, 1, NULL, 1, '2026-01-15 09:22:46', '2026-01-15 09:22:46');

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
(22, '2027_01_15_000000_create_practice_periods_table', 15),
(23, '2026_02_24_000000_unify_teachers_table', 16),
(24, '2026_02_26_000000_create_holidays_table', 17),
(25, '2026_02_27_000000_seed_teachers_list', 18),
(26, '2027_02_01_000000_update_first_course_subjects_group_type', 19),
(27, '2027_02_02_000000_add_group_type_to_first_course_groups', 20),
(28, '2027_02_03_000000_fix_ru_groups_group_type', 21),
(29, '2027_02_05_000000_make_form_two_normatives_teacher_nullable', 22),
(30, '2027_02_10_000000_add_has_subgroups_to_course_groups', 23),
(31, '2027_02_10_010000_add_group_type_to_other_course_groups', 24),
(32, '2027_03_01_000000_create_course_teacher_subjects_tables', 24),
(33, '2027_03_15_000000_seed_first_course_teacher_subjects', 24),
(34, '2027_04_01_000000_reset_second_course_subjects', 25),
(35, '2027_04_01_010000_fix_second_course_subjects_for_sib', 26),
(36, '2027_04_01_020000_update_second_course_subject_codes_for_sib', 27),
(37, '2027_05_05_000000_add_second_course_subjects_and_teachers', 28),
(38, '2027_05_05_010000_dedupe_second_course_subjects_sib', 29),
(39, '2027_05_05_020000_normalize_and_dedupe_second_course_subjects', 30),
(40, '2027_05_05_030000_normalize_punctuation_and_dedupe_second_course_subjects', 31);

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
  `type` enum('educational','production') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `room_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `hours_per_day` tinyint UNSIGNED NOT NULL DEFAULT '6',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
-- Структура таблицы `second_course_group`
--

CREATE TABLE `second_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_subgroups` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_type` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `second_course_group`
--

INSERT INTO `second_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `has_subgroups`, `created_at`, `updated_at`, `group_type`) VALUES
(52, 'ТЭ-214', 214, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(53, 'М-214', 214, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(54, 'М-224', 224, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(55, 'М-234', 234, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(56, 'БКЕ-214', 214, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(57, 'БКЕ-224', 224, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(58, 'ПО-234', 234, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(59, 'ПО-244', 244, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(60, 'ПО-254', 254, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(61, 'ПО-264', 264, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(62, 'ПО-274', 274, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(63, 'ПО-284', 284, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(64, 'АКЖ-214', 214, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(65, 'СИБ-224', 224, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(66, 'СИБ-234', 234, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(67, 'СИБ-244', 244, NULL, 0, NULL, '2026-01-23 01:07:24', 'ru');

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
(79, 'ООМ 01', 1, 'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни', 'Укреплять здоровье и соблюдать принципы здорового образа жизни', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(80, 'ООМ 02', 2, 'РО 2.1 Владеть основами информационно-коммуникационных технологий', 'Владеть основами информационно-коммуникационных технологий', 'Ақпараттық-коммуникациялық технологиялар негіздерін меңгеру', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(81, 'ООМ 02', 2, 'РО 2.2 Использовать услуги информационно-справочных и интерактивных веб-порталов', 'Использовать услуги информационно-справочных и интерактивных веб-порталов', 'Ақпараттық, анықтамалық және интерактивті веб-порталдардың қызметтерін пайдалану', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(82, 'ООМ 03', 3, 'РО 3.1 Владеть основными вопросами в области экономической теории', 'Владеть основными вопросами в области экономической теории', 'Экономикалық теория саласындағы негізгі мәселелерді білу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(83, 'ПМ 03', 3, 'РО 3.2 Создавать, поддерживать, контролировать и осуществлять постоянный мониторинг социальных сетей', 'Создавать, поддерживать, контролировать и осуществлять постоянный мониторинг социальных сетей', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(85, 'ООМ 03', 3, 'РО 3.4 Анализировать и оценивать экономические процессы, происходящие на предприятии', 'Анализировать и оценивать экономические процессы, происходящие на предприятии', NULL, '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(86, 'ПМ 01', 1, 'РО 1.1 Производить монтаж сетевого и серверного оборудования, систем видеонаблюдения и систем контроля управления данными', 'Производить монтаж сетевого и серверного оборудования, систем видеонаблюдения и систем контроля управления данными', 'Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(87, 'ПМ 01', 1, 'РО 1.2 Конфигурировать сетевые сервисы и сетевое оборудование', 'Конфигурировать сетевые сервисы и сетевое оборудование', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(88, 'ПМ 01', 1, 'РО 1.3 Разрабатывать дизайн виртуальных локаций (VR/AR/MR) и обеспечивать их редактирование', 'Разрабатывать дизайн виртуальных локаций (VR/AR/MR) и обеспечивать их редактирование', 'Виртуалды орындардың (VR/AR/MR) дизайнын әзірлеу және өңдеу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(89, 'ПМ 01', 1, 'РО 1.4 Применять теории мотивации и корпоративной культуры', 'Применять теории мотивации и корпоративной культуры', 'Мотивация теориялары мен корпоративтік мәдениетті қолдану', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(90, 'ПМ 01', 1, 'РО 1.5 Автоматизировать задачи обслуживания информационных систем', 'Автоматизировать задачи обслуживания информационных систем', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(91, 'ПМ 02', 2, 'РО 2.1 Разрабатывать визуальное представление сайта', 'Разрабатывать визуальное представление сайта', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(92, 'ПМ 02', 2, 'РО 2.2 Разрабатывать функциональные возможности сайта', 'Разрабатывать функциональные возможности сайта', 'Сайттың функционалдық мүмкіндіктерін әзірлеу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(93, 'ПМ 02', 2, 'РО 2.3 Администрировать Web-ресурсы', 'Администрировать Web-ресурсы', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(94, 'ПМ 02', 2, 'РО 2.4 Применять терминологию на государственном языке при разработке и администрировании web-ресурсов', 'Применять терминологию на государственном языке при разработке и администрировании web-ресурсов', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(95, 'ПМ 02', 2, 'РО 2.5 Применять иностранную терминологию при разработке и администрировании web-ресурсов', 'Применять иностранную терминологию при разработке и администрировании web-ресурсов', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(96, 'ПМ 03', 3, 'РО 3.1 Разрабатывать программные решения на языках программирования', 'Разрабатывать программные решения на языках программирования', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(97, 'ПМ 03', 3, 'РО 3.2 Разрабатывать, внедрять и сопровождать программные решения автоматизированных информационных систем', 'Разрабатывать, внедрять и сопровождать программные решения автоматизированных информационных систем', 'Автоматтандырылған ақпараттық жүйелердің бағдарламалық шешімдерін әзірлеу, енгізу және сүйемелдеу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(98, 'ПМ 03', 3, 'РО 3.3 Разрабатывать программные решения для мобильных устройств', 'Разрабатывать программные решения для мобильных устройств', 'Мобильді құрылғыларға арналған бағдарламалық шешімдерді әзірлеу', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(100, 'БМ 03', 3, 'РО 3.1 Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', 'Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', 'Әлемдік экономиканың даму тенденцияларын, мемлекеттің \\\"жасыл\\\" экономикаға көшуінің негізгі міндеттерін түсіну', '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(101, 'Практика', NULL, 'Производственная практика', 'Производственная практика', NULL, '2026-01-22 08:17:56', '2026-01-22 08:17:56'),
(102, NULL, NULL, 'РО 2.2 Администрировать базы данных', 'Администрировать базы данных.', NULL, '2026-01-22 08:56:59', '2026-01-22 08:56:59'),
(103, NULL, NULL, 'РО 1.4 Интегрировать облачную инфраструктуры с сервисами предприятия', 'Интегрировать облачную инфраструктуры с сервисами предприятия.', NULL, '2026-01-22 08:56:59', '2026-01-22 08:56:59'),
(104, NULL, NULL, 'РО 1.3 Обеспечивать информационную безопасность', 'Обеспечивать информационную безопасность.', NULL, '2026-01-22 08:56:59', '2026-01-22 08:56:59'),
(105, NULL, NULL, 'РО 2.1 Разрабатывать скрипты для автоматизации задач администрирования', 'Разрабатывать скрипты для автоматизации задач администрирования.', NULL, '2026-01-22 08:56:59', '2026-01-22 08:56:59'),
(106, NULL, NULL, 'РО 2.4 Создавать системные приложения', 'Создавать системные приложения.', NULL, '2026-01-22 08:56:59', '2026-01-22 08:56:59'),
(107, 'ООМ 03', 3, 'РО 3.3 Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', 'Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(108, 'ООМ 03', 3, 'РО 3.4 Владеть научными и законодательными основами организации и ведения предпринимательской деятельности в Республике Казахстан', 'Владеть научными и законодательными основами организации и ведения предпринимательской деятельности в Республике Казахстан', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(109, 'ООМ 03', 3, 'РО 3.5 Соблюдать этику делового общения', 'Соблюдать этику делового общения', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(110, 'ООМ 03', 3, 'РО 3.2 Анализировать и оценивать экономические процессы, происходящие на предприятии', 'Анализировать и оценивать экономические процессы, происходящие на предприятии', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(111, 'ООМ 04', 4, 'РО 4.1 Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию', 'Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(112, 'ООМ 04', 4, 'РО 4.2 Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации', 'Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(113, 'ООМ 04', 4, 'РО 4.3 Владеть сведениями об основных отраслях права', 'Владеть сведениями об основных отраслях права', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(114, 'ООМ 04', 4, 'РО 4.4 Владеть основными понятиями социологии и политологии', 'Владеть основными понятиями социологии и политологии', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(115, 'ПМ 01', 1, 'РО 1.1 Разрабатывать план монтажа с изложением оперативно-технической документации', 'Разрабатывать план монтажа с изложением оперативно-технической документации', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(116, 'ПМ 01', 1, 'РО 1.2 Организовывать условия труда на производстве, соответствующие современным стандартам экологической и промышленной безопасности', 'Организовывать условия труда на производстве, соответствующие современным стандартам экологической и промышленной безопасности', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(117, 'ПМ 01', 1, 'РО 1.3 Применять правила технического обслуживания электрооборудования, электроизмерительных приборов, инструментов и приспособлений', 'Применять правила технического обслуживания электрооборудования, электроизмерительных приборов, инструментов и приспособлений', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(118, 'ПМ 02', 2, 'РО 2.1 Организовывать и анализировать ситуации работ по переходу от монтажа к наладке с разработкой соответствующей документации', 'Организовывать и анализировать ситуации работ по переходу от монтажа к наладке с разработкой соответствующей документации', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(119, 'ПМ 02', 2, 'РО 2.2 Проводить расчеты в сфере организации и контроля строительно-монтажных работ', 'Проводить расчеты в сфере организации и контроля строительно-монтажных работ', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(120, 'ПМ 02', 2, 'РО 2.3 Выполнять настройку автоматики на основе знаний выбора подходящей технологии для монтажных работ', 'Выполнять настройку автоматики на основе знаний выбора подходящей технологии для монтажных работ', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(121, 'ПМ 03', 3, 'РО 3.2 Работать с программным компьютерным обеспечением и современными средствами связи при ремонте электрооборудования', 'Работать с программным компьютерным обеспечением и современными средствами связи при ремонте электрооборудования', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(122, 'ПМ 03', 3, 'РО 3.3 Проводить расчеты в сфере организации контроля строительно-монтажных работ, соответственно нормам, стандартам, инструкциям и схемам', 'Проводить расчеты в сфере организации контроля строительно-монтажных работ, соответственно нормам, стандартам, инструкциям и схемам', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(123, 'ПМ 03', 3, 'РО 3.4 Проводить ремонт внутрицеховых сетей и осветительных электроустановок', 'Проводить ремонт внутрицеховых сетей и осветительных электроустановок', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(124, 'ПМ 03', 3, 'РО 3.5 Проводить техническую эксплуатацию, ремонт кабельных и воздушных линий', 'Проводить техническую эксплуатацию, ремонт кабельных и воздушных линий', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(125, 'ПМ 03', 3, 'РО 3.6 Проводить техническую эксплуатацию и ремонт электрических машин и пусконаладочной аппаратуры', 'Проводить техническую эксплуатацию и ремонт электрических машин и пусконаладочной аппаратуры', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(126, 'ПМ 03', 3, 'РО 3.7 Проводить техническую эксплуатацию и ремонт электрооборудования трансформаторов', 'Проводить техническую эксплуатацию и ремонт электрооборудования трансформаторов', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(127, 'ПМ 02', 2, 'РО 2.2 Разрабатывать стратегии привлечения клиентов с целью увеличения объемов продаж, в том числе через Интернет', 'Разрабатывать стратегии привлечения клиентов с целью увеличения объемов продаж, в том числе через Интернет', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(128, 'ПМ 02', 2, 'РО 2.3 Контролировать и прогнозировать цикл продаж', 'Контролировать и прогнозировать цикл продаж', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(129, 'ПМ 02', 2, 'РО 2.4 Разрабатывать планы презентаций продукта, PR-акций, рекламных акций по стимулированию продаж', 'Разрабатывать планы презентаций продукта, PR-акций, рекламных акций по стимулированию продаж', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(130, 'ПМ 02', 2, 'РО 2.5 Устанавливать и поддерживать контакты с клиентами', 'Устанавливать и поддерживать контакты с клиентами', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(131, 'ПМ 03', 3, 'РО 3.1 Подбирать оптимальные программы продвижения', 'Подбирать оптимальные программы продвижения', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(132, 'ПМ 01', 1, 'РО 1.1 Владеть принципами и методами обработки графики для различных целей', 'Владеть принципами и методами обработки графики для различных целей', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(133, 'ПМ 01', 1, 'РО 1.2 Определять стратегию дизайна и разрабатывать макеты пользовательского интерфейса относительно функциональности ПО', 'Определять стратегию дизайна и разрабатывать макеты пользовательского интерфейса относительно функциональности ПО', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(134, 'ПМ 02', 2, 'РО 2.3 Конструировать функциональные возможности сайта', 'Конструировать функциональные возможности сайта', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(139, 'ЖММ 01', 1, 'ОН 1.1 Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау', NULL, 'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(140, 'ЖММ 04', 4, 'ОН 4.1 Толеранттылық пен белсенді жеке ұстанымды қалыптастыратын моральдық құндылықтар мен нормаларды түсіну', NULL, 'Толеранттылық пен белсенді жеке ұстанымды қалыптастыратын моральдық құндылықтар мен нормаларды түсіну', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(141, 'ЖММ 04', 4, 'ОН 4.2 Қазақстан Республикасы халықтары мәдениетінің әлемдік өркениеттегі рөлі мен орнын түсіну', NULL, 'Қазақстан Республикасы халықтары мәдениетінің әлемдік өркениеттегі рөлі мен орнын түсіну', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(142, 'ЖММ 04', 4, 'ОН 4.3 Құқықтың негізгі салалары туралы мәліметтерді білу', NULL, 'Құқықтың негізгі салалары туралы мәліметтерді білу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(143, 'ЖММ 04', 4, 'ОН 4.4 Әлеуметтану мен саясаттанудың негізгі ұғымдарын меңгеру', NULL, 'Әлеуметтану мен саясаттанудың негізгі ұғымдарын меңгеру', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(144, 'ООМ 03', 3, 'ОН 3.2 Кәсіпорында болып жатқан экономикалық процестерді талдау және бағалау', NULL, 'Кәсіпорында болып жатқан экономикалық процестерді талдау және бағалау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(145, 'КМ 02', 2, 'ОН 2.2 Сату көлемін ұлғайту мақсатында, оның ішінде Интернет арқылы клиенттерді тарту стратегияларын әзірлеу', NULL, 'Сату көлемін ұлғайту мақсатында, оның ішінде Интернет арқылы клиенттерді тарту стратегияларын әзірлеу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(146, 'КМ 02', 2, 'ОН 2.3 Сатылым кезеңін бақылау және болжау', NULL, 'Сатылым кезеңін бақылау және болжау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(147, 'КМ 02', 2, 'ОН 2.4 Сатуды ынталандыру үшін өнімнің презентацияларының, PR акцияларының, жарнамалық акцияларының жоспарларын жасау', NULL, 'Сатуды ынталандыру үшін өнімнің презентацияларының, PR акцияларының, жарнамалық акцияларының жоспарларын жасау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(148, 'КМ 02', 2, 'ОН 2.5 Клиенттермен байланыс орнату және қолдау', NULL, 'Клиенттермен байланыс орнату және қолдау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(149, 'КМ 03', 3, 'ОН 3.1 Оңтайлы жарнамалық бағдарламаларды тандап алу', NULL, 'Оңтайлы жарнамалық бағдарламаларды тандап алу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(150, 'КМ 03', 3, 'ОН 3.2 Әлеуметтік желілердің мониторингін қүру, қолдау, бақылау және үздіксіз іске асыру', NULL, 'Әлеуметтік желілердің мониторингін қүру, қолдау, бақылау және үздіксіз іске асыру', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(151, 'КМ 01', 1, 'ОН 1.1 Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу', NULL, 'Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(152, 'КМ 01', 1, 'ОН 1.3 Ақпараттық қауіпсіздікті қамтамасыз ету', NULL, 'Ақпараттық қауіпсіздікті қамтамасыз ету', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(153, 'КМ 01', 1, 'ОН 1.5 Ақпараттық жүйеге техникалық қызмет көрсету тапсырмаларын автоматтандыру', NULL, 'Ақпараттық жүйеге техникалық қызмет көрсету тапсырмаларын автоматтандыру', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(154, 'КМ 02', 2, 'ОН 2.1 Әкімшілік тапсырмаларын автоматтандыру үшін сценарийлерді әзірлеу', NULL, 'Әкімшілік тапсырмаларын автоматтандыру үшін сценарийлерді әзірлеу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(155, 'КМ 02', 2, 'ОН 2.2 Мәліметтер базасын басқару', NULL, 'Мәліметтер базасын басқару', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(156, 'КМ 02', 2, 'ОН 2.3 Веб-ресурстарды басқару', NULL, 'Веб-ресурстарды басқару', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(157, 'ПМ 01', 1, 'ОН 1.1 Әртүрлі мақсаттағы графиканы өңдеудің принциптері мен әдістерін меңгеру', NULL, 'Әртүрлі мақсаттағы графиканы өңдеудің принциптері мен әдістерін меңгеру', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(158, 'ПМ 01', 1, 'ОН 1.2 Бағдарламалық жасақтаманың функционалдығына қатысты дизайн стратегиясын анықтау', NULL, 'Бағдарламалық жасақтаманың функционалдығына қатысты дизайн стратегиясын анықтау', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(159, 'ПМ 02', 2, 'ОН 2.1 Сайттың көрнекі презентациясын әзірлеу', NULL, 'Сайттың көрнекі презентациясын әзірлеу', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(160, 'ПМ 02', 2, 'ОН 2.3 Сайттың функционалдық мүмкіндіктерін құрастыру', NULL, 'Сайттың функционалдық мүмкіндіктерін құрастыру', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(161, 'ПМ 03', 3, 'ОН 3.1 Бағдарламалық шешімдерді бағдарламалау тілдерінде әзірлеу', NULL, 'Бағдарламалық шешімдерді бағдарламалау тілдерінде әзірлеу', '2026-01-23 00:47:39', '2026-01-23 00:47:39');

-- --------------------------------------------------------

--
-- Структура таблицы `second_course_teacher_subjects`
--

CREATE TABLE `second_course_teacher_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `second_course_teacher_subjects`
--

INSERT INTO `second_course_teacher_subjects` (`id`, `teacher_id`, `subject_id`, `created_at`, `updated_at`) VALUES
(35, 72, 79, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(36, 4, 79, NULL, NULL),
(37, 14, 100, NULL, NULL),
(38, 22, 83, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(39, 14, 83, NULL, NULL),
(40, 33, 79, NULL, NULL),
(41, 58, 91, NULL, NULL),
(42, 26, 96, NULL, NULL),
(43, 5, 79, NULL, NULL),
(44, 54, 91, NULL, NULL),
(45, 41, 96, NULL, NULL),
(46, 48, 79, NULL, NULL),
(47, 14, 85, NULL, NULL),
(48, 23, 91, NULL, NULL),
(49, 44, 94, NULL, NULL),
(50, 50, 94, NULL, NULL),
(51, 17, 95, NULL, NULL),
(52, 42, 95, NULL, NULL),
(53, 20, 96, NULL, NULL),
(54, 26, 91, NULL, NULL),
(55, 46, 94, NULL, NULL),
(56, 28, 95, NULL, NULL),
(57, 52, 91, NULL, NULL),
(58, 57, 94, NULL, NULL),
(59, 18, 96, NULL, NULL),
(60, 51, 79, NULL, NULL),
(61, 23, 96, NULL, NULL),
(62, 41, 86, NULL, NULL),
(63, 13, 85, NULL, NULL),
(64, 32, 90, NULL, NULL),
(65, 59, 93, NULL, NULL),
(66, 6, 79, NULL, NULL),
(67, 34, 87, NULL, NULL),
(68, 22, 85, NULL, NULL),
(69, 59, 86, NULL, NULL),
(70, 18, 103, NULL, NULL),
(71, 59, 102, NULL, NULL),
(72, 18, 106, NULL, NULL),
(74, 75, 79, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(75, 76, 79, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(76, 77, 79, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(78, 79, 107, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(79, 80, 109, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(80, 81, 113, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(81, 79, 110, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(82, 13, 110, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(83, 82, 110, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(84, 69, 115, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(85, 69, 117, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(86, 10, 118, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(87, 83, 119, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(88, 83, 120, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(89, 83, 123, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(90, 10, 124, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(91, 84, 125, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(92, 85, 126, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(93, 86, 127, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(94, 79, 128, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(95, 22, 129, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(96, 79, 129, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(97, 22, 130, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(98, 86, 131, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(99, 79, 83, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(100, 87, 132, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(101, 88, 132, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(102, 89, 132, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(103, 90, 132, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(104, 88, 133, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(105, 89, 133, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(106, 91, 133, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(107, 92, 133, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(108, 93, 91, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(109, 94, 91, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(110, 88, 91, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(111, 95, 134, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(112, 96, 134, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(113, 91, 134, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(114, 97, 134, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(115, 98, 94, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(116, 99, 94, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(117, 100, 94, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(118, 101, 94, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(120, 103, 95, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(121, 104, 95, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(122, 95, 96, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(123, 105, 96, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(124, 93, 96, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(125, 106, 86, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(127, 108, 86, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(128, 109, 87, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(129, 108, 104, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(130, 91, 90, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(131, 109, 105, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(132, 94, 105, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(133, 108, 102, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(134, 91, 102, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(135, 108, 93, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(137, 110, 139, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(138, 76, 139, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(139, 81, 142, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(140, 111, 145, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(141, 79, 146, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(142, 22, 147, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(143, 111, 148, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(144, 111, 149, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(145, 79, 150, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(146, 111, 144, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(147, 22, 144, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(148, 112, 157, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(149, 113, 157, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(150, 112, 158, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(151, 96, 159, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(152, 114, 159, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(153, 114, 160, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(154, 94, 160, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(155, 94, 161, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(156, 113, 161, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(157, 113, 151, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(158, 113, 154, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(159, 96, 155, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(160, 96, 156, '2026-01-23 00:47:39', '2026-01-23 00:47:39');

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
('KIpDH7cp7TAjJ4JFeApz6mbFIEXv76KGOtDdcBE1', NULL, '172.31.0.1', 'Mozilla/5.0 (X11; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXd1VHJuRTI0ZGVxTk9IS25GSEx5cDNSbVVqbEk2REs1VGdGaXF6dCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk2OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvZmlyc3QtY291cnNlL3NjaGVkdWxlL2F2YWlsYWJpbGl0eT9jb3Vyc2U9MSZkYXlfa2V5PSVEMCU5MiVEMSU4MiVEMCVCRSVEMSU4MCVEMCVCRCVEMCVCOCVEMCVCQSZsZXNzb25fbnVtYmVyPTMmbW9kZT1udW1lcmF0b3ImdGVhY2hlcl9pZD0zNiZ0eXBlPXRlYWNoZXImd2Vla19zdGFydD0yMDI2LTAyLTAyIjtzOjU6InJvdXRlIjtzOjI3OiJmaXJzdC5zY2hlZHVsZS5hdmFpbGFiaWxpdHkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769155718);

-- --------------------------------------------------------

--
-- Структура таблицы `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `initials` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `teachers`
--

INSERT INTO `teachers` (`id`, `teacher_name`, `initials`, `created_at`, `updated_at`) VALUES
(1, 'Айнабекова Б.О.', 'Айнабекова Б.О.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(2, 'Айткенова А.М.', 'Айткенова А.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(3, 'Алдажуманов Темирлан Казбекович', 'Алдажуманов Т.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(4, 'Алданов Рысбек Абдурасулович', 'Алданов Р.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(5, 'Альдекенов Талгат Сарсенбыевич', 'Альдекенов Т.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(6, 'Арыкова Алмагуль Аблаевна', 'Арыкова А.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(7, 'Асаимова Карлыгаш Сембековна', 'Асаимова К.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(8, 'Ахмедьянова Айгуль Мутаповна', 'Ахмедьянова А.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(9, 'Ахменова Адия Ерталаповна', 'Ахменова А.Е.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(10, 'Ашимова А.К.', 'Ашимова А.К.', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(12, 'Баймухамбетов Батырхан Валиханович', 'Баймухамбетов Б.В.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(13, 'Баширова Г.К.', 'Баширова Г.К.', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(14, 'Бегембетов Дамир Мухтарович', 'Бегембетов Д.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(15, 'Беккер Эрик Эдуардович', 'Беккер Э.Э.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(16, 'Бондарь Виктор Николаевич', 'Бондарь В.Н.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(17, 'Бралина Макпал Достанқызы', 'Бралина М.Д.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(18, 'Брусенко Владислав Сергеевич', 'Брусенко В.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(19, 'Волочаева А.А.', 'Волочаева А.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(20, 'Григорьев Борис Вячеславович', 'Григорьев Б.В.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(21, 'Жагапарова Галия Саматовна', 'Жагапарова Г.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(22, 'Жадрин А.Е.', 'Жадрин А.Е.', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(23, 'Жалпаков Талгат Темиржанович', 'Жалпаков Т.Т.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(24, 'Жамбұл Альбина Қинаятқызы', 'Жамбұл А.Қ.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(25, 'Жуматаева Роза Капышевна', 'Жуматаева Р.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(26, 'Зейнолла Асылбек Арманұлы', 'Зейнолла А.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(27, 'Иванова И.Н.', 'Иванова И.Н.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(28, 'Измайлова Елена Валерьевна', 'Измайлова Е.В.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(29, 'Исаханова Жанар Газизовна', 'Исаханова Ж.Г.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(30, 'Исканова Г.Ш.', 'Исканова Г.Ш.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(31, 'Канагатова Макпал Серикжановна', 'Канагатова М.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(32, 'Кекина Елена Александровна', 'Кекина Е.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(33, 'Косбармаков Адиль Дюсенбаевич', 'Косбармаков А.Д.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(34, 'Крыжановский Станислав Александрович', 'Крыжановский С.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(35, 'Ксембаева Динара Магмуровна', 'Ксембаева Д.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(36, 'Кульмуратов А.К.', 'Кульмуратов А.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(37, 'Курмангазина Асем Жумашевна', 'Курмангазина А.Ж.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(38, 'Қимадиден Гүлайым Ақихатқызы', 'Қимадиден Г.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(39, 'Льясова Айгуль Ауталиповна', 'Льясова А.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(40, 'Мадениятова Гульназ Дарханкызы', 'Мадениятова Г.Д.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(41, 'Мирбеков Бауыржан Сайдуалиулы', 'Мирбеков Б.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(42, 'Мухамеджанова Карина Бауржановна', 'Мухамеджанова К.Б.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(43, 'Мухамедьярова Анар Иматаевна', 'Мухамедьярова А.И.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(44, 'Мынгышева Акжаркын Амангельдиновна', 'Мынгышева А.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(45, 'Нестеров Илья Юрьевич', 'Нестеров И.Ю.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(46, 'Нурмагамбетова Ляззат Бейбитовна', 'Нурмагамбетова Л.Б.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(47, 'Нурмагамбетова Назымгуль Сагындыковна', 'Нурмагамбетова Н.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(48, 'Окенов Руслан Нариманович', 'Окенов Р.Н.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(49, 'Олейник Светлана Александровна', 'Олейник С.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(50, 'Рахметова Майя Агыбаевна', 'Рахметова М.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(51, 'Серёгина Екатерина Анатольевна', 'Серёгина Е.А.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(52, 'Смурыгин Антон Михайлович', 'Смурыгин А.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(53, 'Солтанова Алмагуль Мергеновна', 'Солтанова А.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(54, 'Сулейменова Камила Муратовна', 'Сулейменова К.М.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(55, 'Султангазинова Диана Сериковна', 'Султангазинова Д.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(56, 'Табулдинов Байтас Кайрбаевич', 'Табулдинов Б.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(57, 'Тауымова Айдана Ерболовна', 'Тауымова А.Е.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(58, 'Ташимов Даурен Кабдешович', 'Ташимов Д.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(59, 'Тетерина Светлана Владимировна', 'Тетерина С.В.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(60, 'Трубецкая Татьяна Николаевна', 'Трубецкая Т.Н.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(61, 'Малгаждарова Мира Кошербаевна', 'Малгаждарова М.К.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(62, 'Хаипергина Айгерим Юрьевна', 'Хаипергина А.Ю.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(63, 'Шамгунова Алия Ермековна', 'Шамгунова А.Е.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(64, 'Шандыбасова Аружан Саятовна', 'Шандыбасова А.С.', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(65, 'Физкультура (вакансия)', 'Физкультура', '2026-01-15 09:56:13', '2026-01-15 09:56:13'),
(66, 'Карпаева Л.Б.', 'Карпаева Л.Б.', '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(67, 'Абенов Е.М.', 'Абенов Е.М.', '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(68, 'Аяпберген Н.Е.', 'Аяпберген Н.Е.', '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(69, 'Пилипенко А.А.', 'Пилипенко А.А.', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(70, 'Жотеков А.Ш.', 'Жотеков А.Ш.', '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(71, 'Нұрпеіс Н.Т.', 'Нұрпеіс Н.Т.', '2026-01-22 01:35:44', '2026-01-22 01:35:44'),
(72, 'Алдажуманов Т.К.', 'Алдажуманов Т.К.', '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(73, 'Маслёнко М.В.', 'Маслёнко М.В.', '2026-01-22 07:13:38', '2026-01-22 07:13:38'),
(75, 'Окенов Р.Н.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(76, 'Альдекенов Т.С.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(77, 'Серёгина Е.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(79, 'Бегембетов Д.М.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(80, 'Исканова Г.Е.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(81, 'Льясова А.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(82, 'вакансия/Жадрин А.Е.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(83, 'Канагатова М.С.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(84, 'практика/Пономаренко', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(85, 'вакансия/Акжолов С.М.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(86, 'Шамгунова А.Е.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(87, 'Олейник С.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(88, 'Смурыгин А.М.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(89, 'Исаханова Ж.Г.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(90, 'вакансия/Григорьев Б.В.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(91, 'Кекина Е.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(92, 'вакансия\\Қимадиден Г.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(93, 'Жалпаков Т.Т.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(94, 'Зейнолла А.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(95, 'Григорьев Б.В.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(96, 'Ташимов Д.К.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(97, 'вакансия\\Зейнолла А.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(98, 'Мынгышева А.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(99, 'Рахметова М.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(100, 'Нурмагамбетова Л.Б.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(101, 'Тауымова А.Е.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(103, 'Мухамеджанова К.Б.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(104, 'Измайлова Е.В.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(105, 'Брусенко В.С.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(106, 'Крыжановский С.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(108, 'Тетерина С.В.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(109, 'вакансия/Крыжановский С.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(110, 'Косбармаков А.Д.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(111, 'Канашева А.К.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(112, 'Қимадиден Г.А.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(113, 'Мирбеков Б.С.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(114, 'Сулейменова К.М.', NULL, '2026-01-23 00:47:39', '2026-01-23 00:47:39'),
(115, 'ГД тариф', 'ГД т.', '2026-01-23 02:15:51', '2026-01-23 02:15:51'),
(116, 'ГС', 'ГС', '2026-01-23 03:31:32', '2026-01-23 03:31:32'),
(117, 'Ахмедьянова А.М.', 'Ахмедьянова А.М.', '2026-01-23 05:34:57', '2026-01-23 05:34:57');

-- --------------------------------------------------------

--
-- Структура таблицы `third_course_group`
--

CREATE TABLE `third_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_subgroups` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_type` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `third_course_group`
--

INSERT INTO `third_course_group` (`id`, `group_name`, `group_number`, `subgroup`, `has_subgroups`, `created_at`, `updated_at`, `group_type`) VALUES
(1, 'ТЭ-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(2, 'ТЭ-323', 323, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(3, 'М-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(4, 'М-323', 323, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(5, 'М-333', 333, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(6, 'М-343', 343, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(7, 'БКЕ-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(8, 'БКЕ-323', 323, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(9, 'БКЕ-333', 333, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(10, 'ПО-303', 303, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(11, 'ПО-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(12, 'ПО-323', 323, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(13, 'ПО-333', 333, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(14, 'ПО-343', 343, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(15, 'ПО-353', 353, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(16, 'ПО-363', 363, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(17, 'ПО-373', 373, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(18, 'ПО-383', 383, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(19, 'ПО-393', 393, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(20, 'АҚЖ-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'kz'),
(21, 'СИБ-313', 313, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(22, 'СИБ-323', 323, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru'),
(23, 'СИБ-333', 333, NULL, 1, NULL, '2026-01-22 01:35:43', 'ru');

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
-- Структура таблицы `third_course_teacher_subjects`
--

CREATE TABLE `third_course_teacher_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_course_group_group_type_index` (`group_type`);

--
-- Индексы таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_course_schedules_room_mode_idx` (`room_id`,`study_day`,`lesson_number`,`mode`),
  ADD KEY `first_course_schedules_room_den_idx` (`room_id_denominator`,`study_day`,`lesson_number`),
  ADD KEY `first_course_schedules_group_week_idx` (`group_id`,`week_start`),
  ADD KEY `first_course_schedules_replaces_schedule_id_foreign` (`replaces_schedule_id`),
  ADD KEY `first_course_schedules_teacher_id_foreign` (`teacher_id`);

--
-- Индексы таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_course_subjects_group_type_index` (`group_type`);

--
-- Индексы таблицы `first_course_teacher_subjects`
--
ALTER TABLE `first_course_teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `first_teacher_subject_uniq` (`teacher_id`,`subject_id`),
  ADD KEY `first_teacher_subject_idx` (`subject_id`,`teacher_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fourth_course_group_group_type_index` (`group_type`);

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
-- Индексы таблицы `fourth_course_teacher_subjects`
--
ALTER TABLE `fourth_course_teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fourth_teacher_subject_uniq` (`teacher_id`,`subject_id`),
  ADD KEY `fourth_teacher_subject_idx` (`subject_id`,`teacher_id`);

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
-- Индексы таблицы `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `holidays_year_month_idx` (`year`,`start_month`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `second_course_group_group_type_index` (`group_type`);

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
-- Индексы таблицы `second_course_teacher_subjects`
--
ALTER TABLE `second_course_teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `second_teacher_subject_uniq` (`teacher_id`,`subject_id`),
  ADD KEY `second_teacher_subject_idx` (`subject_id`,`teacher_id`);

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
-- Индексы таблицы `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_teacher_name` (`teacher_name`);

--
-- Индексы таблицы `third_course_group`
--
ALTER TABLE `third_course_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `third_course_group_group_type_index` (`group_type`);

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
-- Индексы таблицы `third_course_teacher_subjects`
--
ALTER TABLE `third_course_teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `third_teacher_subject_uniq` (`teacher_id`,`subject_id`),
  ADD KEY `third_teacher_subject_idx` (`subject_id`,`teacher_id`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT для таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT для таблицы `first_course_teacher_subjects`
--
ALTER TABLE `first_course_teacher_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT для таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=657;

--
-- AUTO_INCREMENT для таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

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
-- AUTO_INCREMENT для таблицы `fourth_course_teacher_subjects`
--
ALTER TABLE `fourth_course_teacher_subjects`
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
-- AUTO_INCREMENT для таблицы `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT для таблицы `practice_periods`
--
ALTER TABLE `practice_periods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT для таблицы `second_course_teacher_subjects`
--
ALTER TABLE `second_course_teacher_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT для таблицы `second_form_two_normatives`
--
ALTER TABLE `second_form_two_normatives`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `second_form_two_records`
--
ALTER TABLE `second_form_two_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

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
-- AUTO_INCREMENT для таблицы `third_course_teacher_subjects`
--
ALTER TABLE `third_course_teacher_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Ограничения внешнего ключа таблицы `first_course_teacher_subjects`
--
ALTER TABLE `first_course_teacher_subjects`
  ADD CONSTRAINT `first_course_teacher_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `first_course_teacher_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `form_two_normatives`
--
ALTER TABLE `form_two_normatives`
  ADD CONSTRAINT `fk_norm_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_norm_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `form_two_records`
--
ALTER TABLE `form_two_records`
  ADD CONSTRAINT `fk_form2_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_form2_subject` FOREIGN KEY (`subject_id`) REFERENCES `first_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `fourth_course_schedules`
--
ALTER TABLE `fourth_course_schedules`
  ADD CONSTRAINT `fourth_course_schedules_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_1_den_foreign` FOREIGN KEY (`replacement_subject_id_1_den`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_1_num_foreign` FOREIGN KEY (`replacement_subject_id_1_num`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_2_den_foreign` FOREIGN KEY (`replacement_subject_id_2_den`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_replacement_subject_id_2_num_foreign` FOREIGN KEY (`replacement_subject_id_2_num`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `fourth_course_teacher_subjects`
--
ALTER TABLE `fourth_course_teacher_subjects`
  ADD CONSTRAINT `fourth_course_teacher_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_course_teacher_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `fourth_form_two_normatives`
--
ALTER TABLE `fourth_form_two_normatives`
  ADD CONSTRAINT `fourth_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `fourth_form_two_records`
--
ALTER TABLE `fourth_form_two_records`
  ADD CONSTRAINT `fourth_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `fourth_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fourth_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fourth_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `fourth_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `schedule_replacements`
--
ALTER TABLE `schedule_replacements`
  ADD CONSTRAINT `fk_repl_group` FOREIGN KEY (`group_id`) REFERENCES `first_course_group` (`id`) ON DELETE CASCADE,
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
  ADD CONSTRAINT `second_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `second_course_teacher_subjects`
--
ALTER TABLE `second_course_teacher_subjects`
  ADD CONSTRAINT `second_course_teacher_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_course_teacher_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `second_form_two_normatives`
--
ALTER TABLE `second_form_two_normatives`
  ADD CONSTRAINT `second_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `second_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `second_form_two_records`
--
ALTER TABLE `second_form_two_records`
  ADD CONSTRAINT `second_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `second_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `second_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `second_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `second_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `third_course_schedules`
--
ALTER TABLE `third_course_schedules`
  ADD CONSTRAINT `third_course_schedules_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_1_den_foreign` FOREIGN KEY (`replacement_subject_id_1_den`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_1_num_foreign` FOREIGN KEY (`replacement_subject_id_1_num`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_2_den_foreign` FOREIGN KEY (`replacement_subject_id_2_den`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_replacement_subject_id_2_num_foreign` FOREIGN KEY (`replacement_subject_id_2_num`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_2_foreign` FOREIGN KEY (`subject_id_2`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_denominator_2_foreign` FOREIGN KEY (`subject_id_denominator_2`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_denominator_foreign` FOREIGN KEY (`subject_id_denominator`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_course_schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `third_course_teacher_subjects`
--
ALTER TABLE `third_course_teacher_subjects`
  ADD CONSTRAINT `third_course_teacher_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_course_teacher_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `third_form_two_normatives`
--
ALTER TABLE `third_form_two_normatives`
  ADD CONSTRAINT `third_form_two_normatives_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_form_two_normatives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `third_form_two_records`
--
ALTER TABLE `third_form_two_records`
  ADD CONSTRAINT `third_form_two_records_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `third_course_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_form_two_records_replacement_subject_id_foreign` FOREIGN KEY (`replacement_subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `third_form_two_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `third_course_subjects` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
