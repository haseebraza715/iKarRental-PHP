# IkarRental PHP

IkarRental is a feature-rich car rental platform built with PHP, designed to provide a seamless user experience for browsing, booking, and managing car rentals. The application features a polished, mobile-responsive interface, robust authentication, and comprehensive admin tools, all enhanced with AJAX for smooth interactions.

## Features

### Homepage
- **Car Listing**: Displays all cars with essential attributes (e.g., model, price, availability).
- **Navigation**: Clicking a car’s card or name navigates to its details page.
- **Filters**: Supports filtering by criteria (e.g., model, price) and date ranges to check availability.

### Car Details Page
- **Detailed Information**: Shows car attributes (e.g., model, features, price) and a high-quality image.
- **Booking System**: Users can book a car for specific dates using a calendar view that restricts selection to available dates.
- **Booking Feedback**: AJAX-driven booking process displays a custom modal (not an alert) with success/failure details (e.g., booking summary, car attributes) without page refresh.

### Authentication
- **Registration**: Secure user registration with error handling for invalid inputs.
- **Login**: Robust login system with error handling for incorrect credentials.
- **Session Awareness**: Pages reflect logged-in status (e.g., displaying username or profile options).
- **Logout**: Accessible from the profile page and all other pages.

### Profile Page
- **User Bookings**: Lists the user’s past and active bookings with details (e.g., car, dates, status).
- **Admin View**: Logged-in admins see all bookings across the platform with options to delete them.

### Admin Features
- **Car Management**:
  - **Create**: Add new cars with validation and error handling (no login required for creation).
  - **Update**: Modify car details with error handling to ensure data integrity.
  - **Delete**: Remove cars with confirmation prompts.
- **Booking Management**: Admins can view and delete any booking via the profile page.

### Design & Usability
- **Polished UI**: Modern, mobile-friendly design optimized for all devices.
- **Interactive Elements**: Calendar-based date picker for bookings and AJAX-powered modals for real-time feedback.
- **Error Handling**: User-friendly error messages for all inputs (e.g., registration, login, car creation, booking).

## Technical Highlights
- **AJAX Integration**: Ensures seamless booking confirmations and feedback via custom modals.
- **Calendar View**: Restricts date selection to available dates, improving usability.
- **Mobile Responsiveness**: Fully optimized for smartphones, tablets, and desktops.
- **Error Handling**: Comprehensive validation across all user and admin actions.

## Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/haseebraza715/ikarrental.git
