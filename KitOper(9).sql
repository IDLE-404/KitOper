-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Ноя 24 2025 г., 07:27
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
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_form2_weeks`
--

CREATE TABLE `first_course_form2_weeks` (
  `id` bigint UNSIGNED NOT NULL,
  `form2_subject_id` bigint UNSIGNED NOT NULL,
  `day_of_month` tinyint UNSIGNED DEFAULT NULL,
  `week_number` tinyint UNSIGNED NOT NULL,
  `hours` decimal(4,1) DEFAULT '0.0',
  `is_weekend` tinyint(1) DEFAULT '0',
  `is_replacement` tinyint(1) DEFAULT '0',
  `replacement_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `replacement_comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_group`
--

CREATE TABLE `first_course_group` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_number` smallint UNSIGNED NOT NULL,
  `subgroup` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(2, 'Понедельник', 2, 12, 24, NULL, 33, NULL, NULL, NULL, '1', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(3, 'Понедельник', 2, 12, 8, NULL, 5, NULL, NULL, NULL, '2', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(4, 'Понедельник', 3, 12, 5, NULL, 27, NULL, NULL, NULL, NULL, 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(5, 'Понедельник', 4, 12, 6, NULL, 33, NULL, NULL, NULL, '1', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(6, 'Понедельник', 4, 12, 6, NULL, 25, NULL, NULL, NULL, '2', 0, '2025-11-23 20:22:07', '2025-11-23 20:22:07'),
(11, 'Понедельник', 1, 13, 12, NULL, 46, NULL, NULL, NULL, '1', 0, '2025-11-24 04:55:28', '2025-11-24 04:55:28'),
(13, 'Понедельник', 1, 12, 12, NULL, 38, NULL, NULL, NULL, '1', 0, '2025-11-24 05:10:17', '2025-11-24 05:10:17');

-- --------------------------------------------------------

--
-- Структура таблицы `first_course_subjects`
--

CREATE TABLE `first_course_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `module_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_index` int DEFAULT NULL,
  `subject_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ru` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kz` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(17, 'ООД 17', 17, 'Қазақ тілі', 'Қазақ тілі', 'Қазақ тілі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(18, 'ООД 18', 18, 'Қазақ әдебиеті', 'Қазақ әдебиеті', 'Қазақ әдебиеті', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(19, 'ООД 19', 19, 'Орыс тілі', 'Орыс тілі', 'Орыс тілі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(20, 'ООД 20', 20, 'Орыс тілі және әдебиеті', 'Орыс тілі және әдебиеті', 'Орыс тілі және әдебиеті', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(21, 'ООД 21', 21, 'Қазақстан тарихы', 'Қазақстан тарихы', 'Қазақстан тарихы', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(22, 'ООД 22', 22, 'Дүниежүзі тарихы', 'Дүниежүзі тарихы', 'Дүниежүзі тарихы', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(23, 'ООД 23', 23, 'Дене тәрбиесі', 'Дене тәрбиесі', 'Дене тәрбиесі', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(24, 'ООД 24', 24, 'Графика және жобалау', 'Графика және жобалау', 'Графика және жобалау', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(25, 'ООД 25', 25, 'БӘжТД', 'БӘжТД', 'БӘжТД', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(26, 'ООД 26', 26, 'НВиТП', 'НВиТП', 'НВиТП', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(27, 'ООД 27', 27, 'НВТП', 'НВТП', 'НВТП', '2025-11-23 14:15:39', '2025-11-23 16:03:20'),
(28, 'ООД 28', 28, 'Шет тілі', 'Шет тілі', 'Шет тілі', '2025-11-23 14:15:39', '2025-11-23 16:03:20');

-- --------------------------------------------------------

--
-- Структура таблицы `frist_course_form2_subjects`
--

CREATE TABLE `frist_course_form2_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `total_hours` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `frist_course_form2_subjects`
--

INSERT INTO `frist_course_form2_subjects` (`id`, `group_id`, `subject_id`, `teacher_id`, `total_hours`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 62, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(2, 1, 2, NULL, 44, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(3, 1, 3, NULL, 62, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(4, 1, 4, NULL, 44, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(5, 1, 5, NULL, 80, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(6, 1, 6, NULL, 40, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(7, 1, 7, NULL, 34, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(8, 1, 8, NULL, 60, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(9, 1, 10, NULL, 44, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(10, 1, 11, NULL, 62, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(11, 1, 12, NULL, 50, '2025-11-24 07:26:44', '2025-11-24 07:26:44'),
(12, 1, 13, NULL, 32, '2025-11-24 07:26:44', '2025-11-24 07:26:44');

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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_23_175206_create_schedule_lessons_table', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `schedule_lessons`
--

CREATE TABLE `schedule_lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL,
  `day_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pair_number` tinyint UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subgroup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_replaced` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('b1kHNglf98s9IayeX6fNd7y3kgEomA3Vt8rbhdCc', NULL, '172.31.0.1', 'Mozilla/5.0 (X11; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVEp0dmVVZ2M0d2YxZmRwSEpjelV3aUlNNk9OWVpNZDVtTUd3OUFhQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9maXJzdC1jb3Vyc2UvZm9ybS10d28iO3M6NToicm91dGUiO3M6MjM6ImZpcnN0LnNjaGVkdWxlLmZvcm1fdHdvIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763969219);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Индексы таблицы `first_course_form2_weeks`
--
ALTER TABLE `first_course_form2_weeks`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `first_course_group`
--
ALTER TABLE `first_course_group`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `first_course_schedules`
--
ALTER TABLE `first_course_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `frist_course_form2_subjects`
--
ALTER TABLE `frist_course_form2_subjects`
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
-- AUTO_INCREMENT для таблицы `first_course_form2_weeks`
--
ALTER TABLE `first_course_form2_weeks`
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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `first_course_subjects`
--
ALTER TABLE `first_course_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `frist_course_form2_subjects`
--
ALTER TABLE `frist_course_form2_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `schedule_lessons`
--
ALTER TABLE `schedule_lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
