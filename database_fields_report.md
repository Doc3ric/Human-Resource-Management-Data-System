# Database Fields Report

This report provides a comprehensive list of all existing tables and their field names (with data types) in the database.

## Table: `activity_log`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `log_name` | varchar |
| `description` | text |
| `subject_type` | varchar |
| `event` | varchar |
| `subject_id` | bigint |
| `causer_type` | varchar |
| `causer_id` | bigint |
| `properties` | json |
| `batch_uuid` | char |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `activity_logs`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `user_id` | bigint |
| `action` | varchar |
| `description` | text |
| `snapshot` | json |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `appointments`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `employee_id` | bigint |
| `position_id` | bigint |
| `appointment_start` | date |
| `appointment_end` | date |
| `date_of_last_promotion` | date |
| `appointment_type` | varchar |
| `remarks` | text |
| `status` | enum |
| `employment_status` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `cache`
| Field Name | Data Type |
| --- | --- |
| `key` | varchar |
| `value` | mediumtext |
| `expiration` | int |

## Table: `cache_locks`
| Field Name | Data Type |
| --- | --- |
| `key` | varchar |
| `owner` | varchar |
| `expiration` | int |

## Table: `contract_renewals`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `plantilla_record_id` | bigint |
| `contract_start_date` | date |
| `contract_end_date` | date |
| `rate` | decimal |
| `rate_type` | enum |
| `renewed_by` | bigint |
| `notes` | text |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `employee_attachments`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `plantilla_record_id` | bigint |
| `file_name` | varchar |
| `file_path` | varchar |
| `file_type` | varchar |
| `document_type` | varchar |
| `uploaded_by` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `employees`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `first_name` | varchar |
| `middle_name` | varchar |
| `last_name` | varchar |
| `employee_number` | varchar |
| `date_of_birth` | date |
| `gender` | enum |
| `email` | varchar |
| `tin` | varchar |
| `phone` | varchar |
| `address` | text |
| `civil_status` | varchar |
| `date_hired` | date |
| `status` | enum |
| `civil_service_eligibility` | varchar |
| `pwd` | tinyint |
| `indigenous_people` | varchar |
| `solo_parent` | varchar |
| `gsis_bp_number` | varchar |
| `umid` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `failed_jobs`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `uuid` | varchar |
| `connection` | text |
| `queue` | text |
| `payload` | longtext |
| `exception` | longtext |
| `failed_at` | timestamp |

## Table: `job_batches`
| Field Name | Data Type |
| --- | --- |
| `id` | varchar |
| `name` | varchar |
| `total_jobs` | int |
| `pending_jobs` | int |
| `failed_jobs` | int |
| `failed_job_ids` | longtext |
| `options` | mediumtext |
| `cancelled_at` | int |
| `created_at` | int |
| `finished_at` | int |

## Table: `jobs`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `queue` | varchar |
| `payload` | longtext |
| `attempts` | tinyint |
| `reserved_at` | int |
| `available_at` | int |
| `created_at` | int |

## Table: `migrations`
| Field Name | Data Type |
| --- | --- |
| `id` | int |
| `migration` | varchar |
| `batch` | int |

## Table: `organizational_units`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `name` | varchar |
| `code` | varchar |
| `status` | enum |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `password_reset_tokens`
| Field Name | Data Type |
| --- | --- |
| `email` | varchar |
| `token` | varchar |
| `created_at` | timestamp |

## Table: `plantilla_records`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `office_department` | varchar |
| `detailed_unit` | varchar |
| `item_no_new` | varchar |
| `position_title` | varchar |
| `salary_grade` | tinyint |
| `authorized_annual_salary` | decimal |
| `step` | tinyint |
| `area_code` | varchar |
| `area_type` | varchar |
| `level` | varchar |
| `last_name` | varchar |
| `first_name` | varchar |
| `middle_name` | varchar |
| `sex` | enum |
| `religion` | varchar |
| `lwop` | int |
| `date_of_birth` | date |
| `tin` | varchar |
| `date_original_appointment` | date |
| `date_original_appt_casual` | date |
| `date_last_promotion` | date |
| `date_last_nolp` | date |
| `loyalty_dismissed_at` | timestamp |
| `employment_status` | varchar |
| `is_health_worker` | tinyint |
| `civil_service_eligibility` | varchar |
| `is_pwd` | tinyint |
| `type_of_disability` | varchar |
| `indigenous_people` | varchar |
| `solo_parent` | varchar |
| `abolished` | tinyint |
| `dissolved` | tinyint |
| `gsis_bp_number` | varchar |
| `position_classification` | varchar |
| `umid` | varchar |
| `employee_code` | varchar |
| `is_vacant` | tinyint |
| `is_apprehended` | tinyint |
| `is_admin_charge` | tinyint |
| `admin_charge_type` | varchar |
| `admin_charges` | json |
| `apprehended_from` | date |
| `admin_charge_from` | date |
| `admin_charge_to` | date |
| `retired_at` | date |
| `nature_of_separation` | varchar |
| `date_separated` | date |
| `nature_of_appointment` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |
| `profile_picture` | varchar |
| `name_extension` | varchar |
| `nature_of_work_detail` | varchar |
| `first_day_of_service` | date |
| `civil_status` | varchar |
| `address` | text |
| `first_level_eligibility` | tinyint |
| `second_level_eligibility` | tinyint |
| `reemployment` | tinyint |
| `item_no_old` | varchar |
| `legislative_district` | varchar |
| `sg_proposed` | tinyint |
| `step_proposed` | tinyint |
| `salary_proposed` | decimal |
| `increase_decrease` | decimal |
| `previous_rate` | decimal |
| `remarks_annotation` | text |
| `base_salary_amount` | decimal |
| `salary_type` | enum |

## Table: `positions`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `organizational_unit_id` | bigint |
| `title` | varchar |
| `code` | varchar |
| `salary_grade` | varchar |
| `authorized_annual_salary` | decimal |
| `actual_annual_salary` | decimal |
| `step` | int |
| `level` | varchar |
| `area_code` | varchar |
| `area_type` | varchar |
| `position_classification` | varchar |
| `abolished` | tinyint |
| `status` | enum |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `salary_grades`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `salary_schedule_id` | bigint |
| `grade` | tinyint |
| `step` | tinyint |
| `monthly_salary` | decimal |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `salary_schedules`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `name` | varchar |
| `law_name` | varchar |
| `lbc_number` | varchar |
| `effective_date` | date |
| `is_active` | tinyint |
| `description` | text |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `deleted_at` | timestamp |

## Table: `service_records`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `plantilla_record_id` | bigint |
| `from_date` | date |
| `to_date` | date |
| `designation` | varchar |
| `status` | varchar |
| `salary` | decimal |
| `station_branch` | varchar |
| `lwp` | varchar |
| `separation_date` | date |
| `cause` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `sessions`
| Field Name | Data Type |
| --- | --- |
| `id` | varchar |
| `user_id` | bigint |
| `ip_address` | varchar |
| `user_agent` | text |
| `payload` | longtext |
| `last_activity` | int |

## Table: `step_increment_histories`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `plantilla_record_id` | bigint |
| `type` | enum |
| `previous_step` | int |
| `new_step` | int |
| `previous_salary_grade` | int |
| `new_salary_grade` | int |
| `previous_annual_salary` | decimal |
| `new_annual_salary` | decimal |
| `effective_date` | date |
| `created_at` | timestamp |
| `updated_at` | timestamp |

## Table: `users`
| Field Name | Data Type |
| --- | --- |
| `id` | bigint |
| `name` | varchar |
| `username` | varchar |
| `email` | varchar |
| `role` | enum |
| `organizational_unit_id` | bigint |
| `profile_picture` | varchar |
| `last_login` | timestamp |
| `status` | enum |
| `is_approved` | tinyint |
| `email_verified_at` | timestamp |
| `password` | varchar |
| `remember_token` | varchar |
| `created_at` | timestamp |
| `updated_at` | timestamp |
| `last_notif_read_at` | timestamp |
| `deleted_at` | timestamp |
