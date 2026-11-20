# ISUFST Attendance Monitoring System

A web-based CRUD attendance monitoring system built with **PHP** and **MySQL** for **Web Systems Technologies** at the **Iloilo State University of Fisheries Science and Technology (ISUFST)**.

- **Developer:** Gemma S. Suelon
- **Live Deployment:** `https://gemmasuelon-attendance.infinityfreeapp.com`
- **Database:** `attendance_db` (`attendance_records` table)
- **Instructor Submission:** `kenthjoshuaespina@isufst.edu.ph` (Submission Window: November 23–27, 2026)

---

## Tech Stack

### Backend & Database
- **PHP (Procedural & MySQLi):** Handles form processing, CRUD operations (`index.php`, `add.php`, `edit.php`, `delete.php`), dynamic filter query construction, and automated attendance status calculation.
- **MySQL / MariaDB:** Relational database storing attendance records (`attendance_db` / `attendance_records`), managed via **phpMyAdmin**.

### Frontend & Styling
- **HTML5 & CSS3:** Semantic markup paired with custom Liquid Glass / Soft UI styling (`backdrop-filter: blur()`, semi-transparent frosted surfaces, rounded cards/pills, and hover elevation transitions).
- **Bootstrap 5.3.3 (CDN):** Responsive grid layout, navbar, summary cards, forms, badges, and responsive data tables, with `data-bs-theme` (`light` / `dark`) integration.
- **JavaScript (Vanilla):** Powers the real-time enlarged date and clock display, theme switching, and cross-page theme persistence via browser `localStorage`.

### Visual Assets & Branding
- **`isufst_lg2.png`:** Transparent ISUFST logo scaled up for clear visibility in the navigation bar.
- **`isufst_bg2.png`:** Full-viewport fixed background image with adaptive light/dark contrast support.

### Development & Hosting Environment
- **XAMPP:** Local development stack (Apache & MySQL) for testing and schema updates.
- **InfinityFree:** Free PHP/MySQL web hosting (`htdocs/`) for live deployment.

---

## Key Features

- **Full CRUD Functionality:** Create, view, edit, and delete student attendance logs across `index.php`, `add.php`, `edit.php`, and `delete.php`.
- **Automated Status Calculation & Cut-Off Rule:**
  - **Present:** `time_in` is on or before `subject_start_time`.
  - **Late:** `time_in` is after `subject_start_time` and on or before `subject_end_time`.
  - **Absent:** `time_in` is after `subject_end_time` (e.g., class ends at `16:00` / 4:00 PM and log-in is at `16:01` / 4:01 PM).
  - **Manual Overrides:** Dropdown supports **Auto-Calculate**, **Absent**, and **Excused**.
- **Combined Search & Filter Bar:** Filter records by keyword (`student_id`, `full_name`, `course_section`), `attendance_date`, `status`, and `course_section`, plus a **Reset** button.
- **Dashboard Summary Cards & Recent Activity Log:** Displays counts for total, present, late, and absent records alongside recent attendance activity.
- **Multi-Page Persistent Light & Dark Mode:** Saves the selected theme in `localStorage` and applies it consistently across `index.php`, `add.php`, and `edit.php`.
- **Enlarged Real-Time Date & Clock:** Displays live date and time in the navigation bar across pages with a ~30% size increase over the baseline size.

---

## Project Structure

```text
├── index.php          # Main dashboard, summary cards, filters, data table, and activity log
├── add.php            # Form and server logic to log new attendance records
├── edit.php           # Form and server logic to update records and recalculate/override status
├── delete.php         # Deletes a selected attendance record by ID
├── db_connect.php     # Database connection script
├── isufst_lg2.png     # ISUFST logo asset
└── isufst_bg2.png     # Background image asset
