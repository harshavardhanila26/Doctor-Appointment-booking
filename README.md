🩺 Doctor Appointment System
This project is a web-based platform designed to efficiently manage the entire lifecycle of medical appointments. It serves as a central hub connecting patients with doctors, providing a seamless experience from booking to follow-up. The system operates with two distinct user roles: patients and doctors, each with a tailored dashboard to manage their specific tasks. Upon logging in, the system intelligently redirects users to their respective dashboards. All user data and appointments are securely stored and managed in a MySQL database.

⭐ Features
Secure Authentication

Dual login system for both patients and doctors.

Passwords are securely stored using modern hashing techniques.

Persistent sessions keep users logged in until they explicitly log out.

A dedicated logout function securely terminates the user session.

Patient Features

Book Appointments: Patients can select a doctor and book an appointment for a specific date and time.

View History: A comprehensive history page lists all past and upcoming appointments, including the doctor's name, specialization, and the status of the appointment.

Reschedule: Patients have the option to reschedule their appointments directly from the history page if the appointment is still in 'booked' status.

Doctor Features

Appointment Dashboard: Doctors are presented with a dashboard listing all their upcoming appointments, showing patient names and contact details.

Manage Appointments: Directly from the dashboard, doctors can mark an appointment as "completed" or "cancelled" with a single click.

View History: Doctors can also view their complete appointment history to track past consultations.

Database & Structure

The system is powered by a MySQL database.

Features a well-defined database schema with separate tables for patients, doctors, and appointments.

Uses foreign keys to ensure data integrity between doctors, patients, and their scheduled appointments.
