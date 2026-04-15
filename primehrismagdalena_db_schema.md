# primehrismagdalena — Database Schema Export
> Engine: MySQL 8.0.45 · Collation: utf8mb4_unicode_ci · Total Tables: 15

---

## 1. `users`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | YES | — | FK → employees.id |
| username | varchar(255) | YES | — | UNIQUE |
| email | varchar(255) | NO | — | UNIQUE |
| password | varchar(255) | NO | — | |
| role | enum('employee','hr','admin') | NO | employee | |
| created_at | timestamp | YES | — | |
| updated_at | timestamp | YES | — | |

---

## 2. `employees`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | varchar(255) | NO | — | UNIQUE |
| first_name | varchar(255) | NO | — | |
| middle_name | varchar(255) | YES | — | |
| last_name | varchar(255) | NO | — | |
| suffix | varchar(255) | YES | — | |
| birth_date | date | NO | — | |
| place_of_birth | varchar(255) | YES | — | |
| sex | enum('Male','Female') | NO | — | |
| civil_status | enum('Single','Married','Widowed','Separated','Divorced') | NO | — | |
| citizenship | varchar(255) | YES | — | |
| height | decimal(5,2) | YES | — | |
| weight | decimal(5,2) | YES | — | |
| blood_type | varchar(5) | YES | — | |
| email | varchar(255) | YES | — | UNIQUE |
| photo | varchar(255) | YES | — | |
| created_at | timestamp | NO | CURRENT_TIMESTAMP | |

---

## 3. `departments`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| code | varchar(20) | NO | — | UNIQUE |
| name | varchar(255) | NO | — | |
| head | varchar(255) | NO | — | |
| personnel_count | int | NO | 0 | |
| status | enum('Active','Inactive') | NO | Active | |
| description | text | YES | — | |
| created_at | timestamp | YES | — | |
| updated_at | timestamp | YES | — | |

---

## 4. `employment_details`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| position | varchar(255) | YES | — | |
| department | varchar(255) | YES | — | |
| employment_status | varchar(255) | YES | — | |
| appointment_date | date | YES | — | |
| salary_grade | varchar(255) | YES | — | |
| step_increment | varchar(255) | YES | — | |

---

## 5. `addresses`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| type | enum('residential','permanent') | NO | — | |
| house_no | varchar(255) | YES | — | |
| street | varchar(255) | YES | — | |
| barangay | varchar(255) | YES | — | |
| city | varchar(255) | YES | — | |
| province | varchar(255) | YES | — | |
| zip_code | varchar(255) | YES | — | |

---

## 6. `contacts`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| type | enum('mobile','landline','emergency') | NO | — | |
| number | varchar(255) | NO | — | |
| contact_person | varchar(255) | YES | — | |

---

## 7. `government_ids`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| gsis_no | varchar(255) | YES | — | |
| philhealth_no | varchar(255) | YES | — | |
| pagibig_no | varchar(255) | YES | — | |
| tin_no | varchar(255) | YES | — | |
| license_no | varchar(255) | YES | — | |

---

## 8. `legal_requirements`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| saln_submitted | tinyint(1) | NO | 0 | |
| oath_of_office | tinyint(1) | NO | 0 | |
| assumption_date | date | YES | — | |

---

## 9. `educations`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| level | varchar(255) | YES | — | |
| school_name | varchar(255) | YES | — | |
| degree | varchar(255) | YES | — | |
| year_graduated | varchar(255) | YES | — | |
| honors | varchar(255) | YES | — | |

---

## 10. `eligibilities`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| type | varchar(255) | YES | — | |
| rating | varchar(255) | YES | — | |
| exam_date | date | YES | — | |
| exam_place | varchar(255) | YES | — | |
| license_no | varchar(255) | YES | — | |
| validity_date | date | YES | — | |

---

## 11. `work_experiences`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| company_name | varchar(255) | YES | — | |
| position | varchar(255) | YES | — | |
| from_date | date | YES | — | |
| to_date | date | YES | — | |
| salary | decimal(10,2) | YES | — | |
| appointment_status | varchar(255) | YES | — | |
| is_government | tinyint(1) | NO | 0 | |

---

## 12. `trainings`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| title | varchar(255) | YES | — | |
| date_from | date | YES | — | |
| date_to | date | YES | — | |
| hours | int | YES | — | |
| conducted_by | varchar(255) | YES | — | |

---

## 13. `family_members`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| name | varchar(255) | YES | — | |
| relationship | enum('spouse','father','mother','child') | NO | — | |
| birthdate | date | YES | — | |
| occupation | varchar(255) | YES | — | |

---

## 14. `documents`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | NO | — | PK, Auto Increment |
| employee_id | bigint unsigned | NO | — | FK → employees.id |
| document_type | varchar(255) | YES | — | |
| file_path | varchar(255) | YES | — | |
| status | enum('pending','approved','rejected') | NO | pending | |
| uploaded_at | timestamp | NO | CURRENT_TIMESTAMP | |

---

## 15. `sessions`
| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| id | varchar(255) | NO | — | PK |
| user_id | bigint unsigned | YES | — | |
| ip_address | varchar(45) | YES | — | |
| user_agent | text | YES | — | |
| payload | longtext | NO | — | |
| last_activity | int | NO | — | |

---

## Foreign Key Relationships Summary
| Table | Column | References |
|---|---|---|
| users | employee_id | employees.id (CASCADE DELETE) |
| addresses | employee_id | employees.id (CASCADE DELETE) |
| contacts | employee_id | employees.id (CASCADE DELETE) |
| documents | employee_id | employees.id (CASCADE DELETE) |
| educations | employee_id | employees.id (CASCADE DELETE) |
| eligibilities | employee_id | employees.id (CASCADE DELETE) |
| employment_details | employee_id | employees.id (CASCADE DELETE) |
| family_members | employee_id | employees.id (CASCADE DELETE) |
| government_ids | employee_id | employees.id (CASCADE DELETE) |
| legal_requirements | employee_id | employees.id (CASCADE DELETE) |
| trainings | employee_id | employees.id (CASCADE DELETE) |
| work_experiences | employee_id | employees.id (CASCADE DELETE) |
