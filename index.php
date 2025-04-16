<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toothly Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(76, 175, 80, 0.1);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(46, 125, 50, 0.7);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(46, 125, 50, 0.9);
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Translucent effects */
        .translucent-card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
        
        /* Color theme */
        .bg-primary {
            background-color: #4CAF50;
        }
        .bg-primary-dark {
            background-color: #2E7D32;
        }
        .text-primary {
            color: #4CAF50;
        }
        .text-primary-dark {
            color: #2E7D32;
        }
        .border-primary {
            border-color: #4CAF50;
        }
        .hover\:bg-primary:hover {
            background-color: #4CAF50;
        }
        .hover\:bg-primary-dark:hover {
            background-color: #2E7D32;
        }
        .focus\:ring-primary:focus {
            --tw-ring-color: #4CAF50;
        }
        
        /* Image container */
        .image-container {
            width: 100%;
            height: 400px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            background: linear-gradient(145deg, #E8F5E9, #C8E6C9);
            position: relative;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        
        .modal.show .modal-content {
            transform: translateY(0);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .close:hover {
            color: black;
        }
        
        /* Animated elements */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        /* Service card hover effect */
        .service-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #4CAF50;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .service-card:hover::after {
            transform: scaleX(1);
        }
        
        /* Testimonial styles */
        .testimonial-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Floating action button */
        .fab-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            z-index: 40;
            transition: all 0.3s ease;
        }
        
        .fab-button:hover {
            background-color: #2E7D32;
            transform: scale(1.1);
        }
        
        /* Pulse animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Hero image overlay */
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
        }
        
        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-green-100 min-h-screen">
    <!-- Floating Action Button -->
    <div class="fab-button animate-float" onclick="openModal()">
        <i class="fas fa-calendar-plus text-2xl"></i>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-30 transition-all duration-300">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-tooth text-primary text-2xl"></i>
                <h1 class="text-xl font-bold text-primary-dark">Toothly</h1>
            </div>
            <nav>
                <ul class="flex items-center space-x-6">
                    <li><a href="#home" class="text-primary hover:text-primary-dark font-medium transition-colors">Home</a></li>
                    <li><a href="#services" class="text-gray-600 hover:text-primary-dark transition-colors">Services</a></li>
                    <li><a href="#about" class="text-gray-600 hover:text-primary-dark transition-colors">About Us</a></li>
                    <li><a href="#contact" class="text-gray-600 hover:text-primary-dark transition-colors">Contact</a></li>
                    <li class="ml-4">
                        <a href="login.php" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg shadow transition transform hover:scale-105">
                            <i class="fas fa-user mr-1"></i> <?php echo isset($_SESSION['user']) ? 'Dashboard' : 'Login/Signup'; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="container mx-auto px-4 py-12" id="home">
        <div class="flex flex-col lg:flex-row items-center">
            <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-10 animate__animated animate__fadeInLeft">
                <h1 class="text-4xl md:text-5xl font-bold text-primary-dark leading-tight mb-6">
                    Your <span class="text-primary">Healthy Smile</span> Is Our Priority
                </h1>
                <p class="text-lg text-gray-700 mb-8">
                    Professional dental care with a gentle touch. Our experienced team provides comprehensive dental services in a comfortable environment.
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="login.php" class="bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300 text-center transform hover:scale-105">
                        <i class="fas fa-sign-in-alt mr-2"></i> <?php echo isset($_SESSION['user']) ? 'Go to Dashboard' : 'Staff Login'; ?>
                    </a>
                    <button onclick="openModal()" class="border border-primary text-primary hover:bg-green-50 font-semibold py-3 px-6 rounded-lg transition duration-300 text-center transform hover:scale-105 pulse">
                        <i class="fas fa-calendar-plus mr-2"></i> Request Appointment
                    </button>
                </div>
                
                <!-- Stats Counter -->
                <div class="mt-12 grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-3xl font-bold text-primary-dark counter" data-target="2500">0</div>
                        <div class="text-gray-600 text-sm mt-1">Happy Patients</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-3xl font-bold text-primary-dark counter" data-target="15">0</div>
                        <div class="text-gray-600 text-sm mt-1">Expert Dentists</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                        <div class="text-3xl font-bold text-primary-dark counter" data-target="10">0</div>
                        <div class="text-gray-600 text-sm mt-1">Years Experience</div>
                    </div>
                </div>
            </div>
            <div class="lg:w-1/2 animate__animated animate__fadeInRight">
                <div class="image-container relative">
                    <img src='images/dentist2.png' alt='Dentist with patient' class='w-full h-full object-cover'>
                    <div class="hero-overlay"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Appointment Request Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-2xl font-bold text-primary-dark mb-6">Request an Appointment</h2>
            <form id="appointmentForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1" for="name">Full Name</label>
                        <input type="text" name="name" id="name" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1" for="email">Email</label>
                        <input type="email" name="email" id="email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1" for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1" for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1" for="preferred_date">Preferred Date</label>
                    <input type="date" name="preferred_date" id="preferred_date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1" for="preferred_time">Preferred Time</label>
                    <select name="preferred_time" id="preferred_time" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                        <option value="">Select Time</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1" for="service">Service Needed</label>
                    <select name="service" id="service" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                        <option value="">Select Service</option>
                        <option value="General Checkup">General Checkup</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Tooth Extraction">Tooth Extraction</option>
                        <option value="Root Canal">Root Canal</option>
                        <option value="Braces/Orthodontics">Braces/Orthodontics</option>
                        <option value="Teeth Whitening">Teeth Whitening</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1" for="message">Additional Information</label>
                    <textarea name="message" id="message" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"></textarea>
                </div>
                <button type="submit" 
                        class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-6 rounded-lg shadow transition flex items-center justify-center transform hover:scale-[1.02]">
                    <span id="submitAppointmentText">Submit Request</span>
                    <svg id="submitAppointmentSpinner" class="hidden ml-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                <div id="appointmentFormMessage" class="hidden p-3 rounded-lg"></div>
            </form>
        </div>
    </div>

    <!-- Services Section -->
    <section class="py-16 bg-white" id="services">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-primary font-medium">OUR SERVICES</span>
                <h2 class="text-3xl font-bold text-primary-dark mt-2">Comprehensive Dental Care</h2>
                <p class="text-gray-600 max-w-2xl mx-auto mt-4">We offer a wide range of dental services to keep your smile healthy and beautiful.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="service-card bg-green-50 p-6 rounded-xl shadow-md hover:shadow-lg">
                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-tooth text-primary text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-primary-dark mb-2">General Dentistry</h3>
                    <p class="text-gray-700 mb-4">Regular checkups, cleanings, and fillings to maintain your oral health.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Dental Exams</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Teeth Cleaning</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Fillings</li>
                    </ul>
                </div>
                <div class="service-card bg-green-50 p-6 rounded-xl shadow-md hover:shadow-lg">
                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-teeth text-primary text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-primary-dark mb-2">Cosmetic Dentistry</h3>
                    <p class="text-gray-700 mb-4">Teeth whitening, veneers, and smile makeovers to enhance your appearance.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Teeth Whitening</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Veneers</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Bonding</li>
                    </ul>
                </div>
                <div class="service-card bg-green-50 p-6 rounded-xl shadow-md hover:shadow-lg">
                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-teeth-open text-primary text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-primary-dark mb-2">Orthodontics</h3>
                    <p class="text-gray-700 mb-4">Braces and aligners to straighten teeth and correct bites.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Traditional Braces</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Invisalign</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-primary mr-2"></i> Retainers</li>
                    </ul>
                </div>
            </div>
            
            <!-- Additional Services Carousel -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-primary-dark text-center mb-8">More Specialized Services</h3>
                <div class="relative">
                    <div class="carousel-container overflow-hidden">
                        <div class="carousel-track flex transition-transform duration-300" id="serviceCarousel">
                            <div class="carousel-slide min-w-full md:min-w-[50%] lg:min-w-[33.333%] px-4">
                                <div class="bg-white p-6 rounded-xl shadow-md h-full">
                                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                                        <i class="fas fa-teeth text-primary text-xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-primary-dark mb-2">Dental Implants</h4>
                                    <p class="text-gray-700">Permanent solution for missing teeth that look and feel natural.</p>
                                </div>
                            </div>
                            <div class="carousel-slide min-w-full md:min-w-[50%] lg:min-w-[33.333%] px-4">
                                <div class="bg-white p-6 rounded-xl shadow-md h-full">
                                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                                        <i class="fas fa-teeth-open text-primary text-xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-primary-dark mb-2">Root Canal Therapy</h4>
                                    <p class="text-gray-700">Save your natural tooth and relieve pain with this treatment.</p>
                                </div>
                            </div>
                            <div class="carousel-slide min-w-full md:min-w-[50%] lg:min-w-[33.333%] px-4">
                                <div class="bg-white p-6 rounded-xl shadow-md h-full">
                                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                                        <i class="fas fa-tooth text-primary text-xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-primary-dark mb-2">Pediatric Dentistry</h4>
                                    <p class="text-gray-700">Specialized care for children's dental health needs.</p>
                                </div>
                            </div>
                            <div class="carousel-slide min-w-full md:min-w-[50%] lg:min-w-[33.333%] px-4">
                                <div class="bg-white p-6 rounded-xl shadow-md h-full">
                                    <div class="bg-green-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                                        <i class="fas fa-teeth text-primary text-xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-primary-dark mb-2">Periodontal Care</h4>
                                    <p class="text-gray-700">Treatment for gum disease to maintain oral health.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow-md z-10 -ml-4">
                        <i class="fas fa-chevron-left text-primary"></i>
                    </button>
                    <button class="carousel-next absolute right-0 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow-md z-10 -mr-4">
                        <i class="fas fa-chevron-right text-primary"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-16 bg-green-50" id="about">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-10 animate__animated animate__fadeInLeft">
                    <span class="text-primary font-medium">ABOUT US</span>
                    <h2 class="text-3xl font-bold text-primary-dark mt-2 mb-6">About Toothly</h2>
                    <p class="text-gray-700 mb-4">
                        Founded in 2015, Toothly has been providing exceptional dental care to our community. Our mission is to deliver personalized, high-quality dental services in a comfortable and welcoming environment.
                    </p>
                    <p class="text-gray-700 mb-6">
                        Our team of experienced dentists and hygienists are committed to staying at the forefront of dental technology and techniques to ensure you receive the best possible care.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="bg-primary text-white p-2 rounded-full mr-4">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-primary-dark">Modern Equipment</h4>
                                <p class="text-gray-700 text-sm">We use the latest dental technology for precise and comfortable treatments.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-primary text-white p-2 rounded-full mr-4">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-primary-dark">Patient-Centered Care</h4>
                                <p class="text-gray-700 text-sm">Your comfort and satisfaction are our top priorities.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-primary text-white p-2 rounded-full mr-4">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-primary-dark">Emergency Services</h4>
                                <p class="text-gray-700 text-sm">We're here for you when you need urgent dental care.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/2 animate__animated animate__fadeInRight">
                    <div class="image-container">
                        <img src='images/clinic.jpg' alt='Dental clinic' class='w-full h-full object-full'>
                    </div>
                    
                    <!-- Team Section -->
                    <div class="mt-8">
                        <h3 class="text-xl font-semibold text-primary-dark mb-4">Meet Our Team</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="bg-white p-2 rounded-full inline-block mb-2">
                                    <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Dr. Smith" class="w-16 h-16 rounded-full object-cover">
                                </div>
                                <h4 class="font-medium text-primary-dark">Dr. Sarah Smith</h4>
                                <p class="text-gray-600 text-sm">General Dentist</p>
                            </div>
                            <div class="text-center">
                                <div class="bg-white p-2 rounded-full inline-block mb-2">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Dr. Johnson" class="w-16 h-16 rounded-full object-cover">
                                </div>
                                <h4 class="font-medium text-primary-dark">Dr. Michael Johnson</h4>
                                <p class="text-gray-600 text-sm">Orthodontist</p>
                            </div>
                            <div class="text-center">
                                <div class="bg-white p-2 rounded-full inline-block mb-2">
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Dr. Lee" class="w-16 h-16 rounded-full object-cover">
                                </div>
                                <h4 class="font-medium text-primary-dark">Dr. Emily Lee</h4>
                                <p class="text-gray-600 text-sm">Pediatric Dentist</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-primary font-medium">TESTIMONIALS</span>
                <h2 class="text-3xl font-bold text-primary-dark mt-2">What Our Patients Say</h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="testimonial-card p-6">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/43.jpg" alt="Patient" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-primary-dark">Jessica T.</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700">"The staff at Toothly is amazing! They made me feel comfortable during my root canal, and the procedure was painless. Highly recommend!"</p>
                </div>
                <div class="testimonial-card p-6">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Patient" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-primary-dark">Robert K.</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700">"I've been coming here for years. The dentists are knowledgeable and take time to explain everything. My teeth have never been healthier!"</p>
                </div>
                <div class="testimonial-card p-6">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/28.jpg" alt="Patient" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-primary-dark">Maria S.</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700">"My kids actually look forward to their dental visits! The pediatric dentist is wonderful with children and makes the experience fun."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-primary-dark text-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose Our Clinic?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-user-md text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Expert Dentists</h3>
                    <p class="text-green-100">Our team of certified professionals provides the highest quality dental care with years of experience.</p>
                </div>
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-clock text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Flexible Hours</h3>
                    <p class="text-green-100">We offer convenient appointment times including evenings and weekends to fit your busy schedule.</p>
                </div>
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-shield-alt text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Modern Equipment</h3>
                    <p class="text-green-100">State-of-the-art technology for precise diagnosis and comfortable treatment with minimal discomfort.</p>
                </div>
            </div>
            
            <!-- Additional Features -->
            <div class="grid md:grid-cols-3 gap-8 mt-8">
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-comments text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Patient Education</h3>
                    <p class="text-green-100">We take time to explain procedures and answer all your questions about dental health.</p>
                </div>
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-heart text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Comfortable Environment</h3>
                    <p class="text-green-100">Our clinic is designed to make you feel relaxed with amenities to ease dental anxiety.</p>
                </div>
                <div class="text-center p-6 bg-green-700 rounded-lg shadow-md transform hover:scale-105 transition">
                    <i class="fas fa-hand-holding-usd text-4xl mb-4 text-green-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Flexible Payment</h3>
                    <p class="text-green-100">We accept most insurance plans and offer flexible payment options for your convenience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-primary font-medium">HAVE QUESTIONS?</span>
                <h2 class="text-3xl font-bold text-primary-dark mt-2">Frequently Asked Questions</h2>
            </div>
            
            <div class="max-w-3xl mx-auto">
                <div class="faq-item mb-4 border-b border-gray-200 pb-4">
                    <button class="faq-question flex justify-between items-center w-full text-left font-medium text-primary-dark hover:text-primary transition-colors">
                        <span>How often should I visit the dentist?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer mt-2 text-gray-700 hidden">
                        <p>We recommend visiting the dentist every six months for a routine checkup and cleaning. However, some patients may need more frequent visits depending on their oral health needs.</p>
                    </div>
                </div>
                
                <div class="faq-item mb-4 border-b border-gray-200 pb-4">
                    <button class="faq-question flex justify-between items-center w-full text-left font-medium text-primary-dark hover:text-primary transition-colors">
                        <span>What should I do in a dental emergency?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer mt-2 text-gray-700 hidden">
                        <p>For dental emergencies like severe pain, knocked-out teeth, or broken restorations, call us immediately at (123) 456-7890. We offer same-day emergency appointments for urgent cases.</p>
                    </div>
                </div>
                
                <div class="faq-item mb-4 border-b border-gray-200 pb-4">
                    <button class="faq-question flex justify-between items-center w-full text-left font-medium text-primary-dark hover:text-primary transition-colors">
                        <span>Do you accept dental insurance?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer mt-2 text-gray-700 hidden">
                        <p>Yes, we accept most major dental insurance plans. Our team will help you understand your coverage and file claims on your behalf. We also offer flexible payment options for patients without insurance.</p>
                    </div>
                </div>
                
                <div class="faq-item mb-4 border-b border-gray-200 pb-4">
                    <button class="faq-question flex justify-between items-center w-full text-left font-medium text-primary-dark hover:text-primary transition-colors">
                        <span>How can I overcome my fear of dentists?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer mt-2 text-gray-700 hidden">
                        <p>We specialize in treating patients with dental anxiety. We offer sedation options, explain each step of the process, and create a calm environment. Many of our formerly anxious patients now look forward to their visits!</p>
                    </div>
                </div>
                
                <div class="faq-item mb-4 border-b border-gray-200 pb-4">
                    <button class="faq-question flex justify-between items-center w-full text-left font-medium text-primary-dark hover:text-primary transition-colors">
                        <span>What's the best way to whiten my teeth?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer mt-2 text-gray-700 hidden">
                        <p>We offer professional teeth whitening treatments that are safer and more effective than over-the-counter products. During your consultation, we'll recommend the best option based on your teeth and desired results.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 bg-white" id="contact">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-primary-dark mb-12">Contact Us</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-semibold text-primary-dark mb-4">Visit Our Clinic</h3>
                    <div class="bg-green-50 p-6 rounded-lg shadow-sm">
                        <p class="text-gray-700 mb-4 flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mr-3 mt-1"></i> 
                            <span>123 Dental Street, Smile City, SC 12345</span>
                        </p>
                        <p class="text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-phone-alt text-primary mr-3"></i> (123) 456-7890
                        </p>
                        <p class="text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-envelope text-primary mr-3"></i> info@toothly.com
                        </p>
                        <p class="text-gray-700 flex items-center">
                            <i class="fas fa-clock text-primary mr-3"></i> Mon-Fri: 8am-6pm, Sat: 9am-2pm
                        </p>
                    </div>
                    
                    <!-- Map -->
                    <div class="mt-6 rounded-lg overflow-hidden shadow-sm">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215373510518!2d-73.98784492453322!3d40.74844097138952!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1623251157754!5m2!1sen!2sus" 
                                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-lg"></iframe>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-primary-dark mb-4">Send Us a Message</h3>
                    <form id="contactForm" class="space-y-4">
                        <div>
                            <input type="text" name="name" placeholder="Your Name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Your Email" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                        </div>
                        <div>
                            <input type="tel" name="phone" placeholder="Your Phone Number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                        </div>
                        <!-- <div>
                            <select name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                                <option value="">Select Subject</option>
                                <option value="Appointment">Appointment Inquiry</option>
                                <option value="General">General Question</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Billing">Billing Inquiry</option>
                            </select>
                        </div> -->
                        <div>
                            <textarea name="message" placeholder="Your Message" rows="4" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"></textarea>
                        </div>
                        <button type="submit" 
                                class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-6 rounded-lg shadow transition flex items-center justify-center transform hover:scale-[1.02]">
                            <span id="submitText">Send Message</span>
                            <svg id="submitSpinner" class="hidden ml-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        <div id="formMessage" class="hidden p-3 rounded-lg"></div>
                    </form>
                    
                    <!-- Social Media -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-primary-dark mb-3">Connect With Us</h4>
                        <div class="flex space-x-4">
                            <a href="#" class="bg-green-100 text-primary-dark hover:bg-primary hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="bg-green-100 text-primary-dark hover:bg-primary hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="bg-green-100 text-primary-dark hover:bg-primary hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="bg-green-100 text-primary-dark hover:bg-primary hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <!-- <section class="py-12 bg-primary-dark text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-4">Subscribe to Our Newsletter</h2>
            <p class="max-w-2xl mx-auto mb-6 text-green-200">Stay updated with dental health tips, special offers, and clinic news.</p>
            <form class="max-w-md mx-auto flex">
                <input type="email" placeholder="Your email address" required
                       class="flex-grow px-4 py-2 rounded-l-lg focus:outline-none text-gray-800">
                <button type="submit" class="bg-primary hover:bg-green-600 text-white font-medium py-2 px-6 rounded-r-lg transition">
                    Subscribe
                </button>
            </form>
        </div>
    </section> -->

    <!-- Footer -->
        <footer class="bg-primary-dark text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Clinic Info -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-tooth text-2xl text-white mr-3"></i>
                        <h2 class="text-2xl font-bold text-white">Toothly</h2>
                    </div>
                    <p class="text-gray-100 mb-4">Quality dental care for your whole family. We're committed to providing exceptional services with a personal touch.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-primary-light text-xl">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-white hover:text-primary-light text-xl">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white hover:text-primary-light text-xl">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-semibold mb-4 text-white border-b border-primary-light pb-2">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-chevron-right text-xs text-white mr-2"></i> Home
                        </a></li>
                        <li><a href="#services" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-chevron-right text-xs text-white mr-2"></i> Services
                        </a></li>
                        <li><a href="#about" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-chevron-right text-xs text-white mr-2"></i> About Us
                        </a></li>
                        <li><a href="#contact" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-chevron-right text-xs text-white mr-2"></i> Contact
                        </a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h3 class="text-xl font-semibold mb-4 text-white border-b border-primary-light pb-2">Services</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-tooth text-white mr-2 text-sm"></i> General Dentistry
                        </a></li>
                        <li><a href="#" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-smile text-white mr-2 text-sm"></i> Cosmetic Dentistry
                        </a></li>
                        <li><a href="#" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-teeth text-white mr-2 text-sm"></i> Orthodontics
                        </a></li>
                        <li><a href="#" class="text-gray-100 hover:text-white transition flex items-center">
                            <i class="fas fa-child text-white mr-2 text-sm"></i> Pediatric Care
                        </a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-xl font-semibold mb-4 text-white border-b border-primary-light pb-2">Contact Us</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-white mt-1 mr-3"></i>
                            <span class="text-gray-100">123 Dental Street<br>Smile City, SC 12345</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt text-white mr-3"></i>
                            <span class="text-gray-100">(123) 456-7890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-white mr-3"></i>
                            <span class="text-gray-100">info@toothly.com</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-clock text-white mt-1 mr-3"></i>
                            <span class="text-gray-100">Mon-Fri: 8am-6pm<br>Sat: 9am-2pm</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-primary-light mt-12 pt-8 text-center">
                <p class="text-gray-200">&copy; 2025 Toothly Dental Clinic. All rights reserved.</p>
                <p class="text-gray-300 text-sm mt-2">Providing quality dental care since 2024</p>
            </div>
        </div>
    </footer>

    <script>
        // Modal functions
        function openModal() {
            const modal = document.getElementById('appointmentModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
        
        function closeModal() {
            const modal = document.getElementById('appointmentModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('appointmentModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Appointment Form Submission
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const submitText = document.getElementById('submitAppointmentText');
            const submitSpinner = document.getElementById('submitAppointmentSpinner');
            const formMessage = document.getElementById('appointmentFormMessage');
            
            // Show loading state
            submitText.textContent = 'Submitting...';
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
            formMessage.classList.add('hidden');
            
            // Submit form data
            fetch('process_appointment_request.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.reset();
                    formMessage.textContent = data.message;
                    formMessage.classList.remove('hidden', 'bg-red-100', 'border-red-400', 'text-red-700');
                    formMessage.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                    submitText.textContent = 'Request Submitted!';
                    
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        closeModal();
                        submitText.textContent = 'Submit Request';
                    }, 2000);
                } else {
                    if (data.errors) {
                        let errorMessages = Object.values(data.errors).join('<br>');
                        formMessage.innerHTML = errorMessages;
                    } else {
                        formMessage.textContent = data.message || 'An error occurred. Please try again.';
                    }
                    formMessage.classList.remove('hidden', 'bg-green-100', 'border-green-400', 'text-green-700');
                    formMessage.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                    submitText.textContent = 'Try Again';
                }
            })
            .catch(error => {
                formMessage.textContent = 'Network error. Please try again later.';
                formMessage.classList.remove('hidden', 'bg-green-100', 'border-green-400', 'text-green-700');
                formMessage.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                submitText.textContent = 'Error - Retry';
            })
            .finally(() => {
                submitSpinner.classList.add('hidden');
                submitBtn.disabled = false;
                setTimeout(() => {
                    submitText.textContent = 'Submit Request';
                }, 3000);
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Contact Form Submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const formMessage = document.getElementById('formMessage');
            
            // Show loading state
            submitText.textContent = 'Sending...';
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
            formMessage.classList.add('hidden');
            
            // Submit form data
            fetch('contact.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.reset();
                    formMessage.textContent = data.message;
                    formMessage.classList.remove('hidden', 'bg-red-100', 'border-red-400', 'text-red-700');
                    formMessage.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                    submitText.textContent = 'Message Sent!';
                } else {
                    if (data.errors) {
                        let errorMessages = Object.values(data.errors).join('<br>');
                        formMessage.innerHTML = errorMessages;
                    } else {
                        formMessage.textContent = data.message || 'An error occurred. Please try again.';
                    }
                    formMessage.classList.remove('hidden', 'bg-green-100', 'border-green-400', 'text-green-700');
                    formMessage.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                    submitText.textContent = 'Try Again';
                }
            })
            .catch(error => {
                formMessage.textContent = 'Network error. Please try again later.';
                formMessage.classList.remove('hidden', 'bg-green-100', 'border-green-400', 'text-green-700');
                formMessage.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                submitText.textContent = 'Error - Retry';
            })
            .finally(() => {
                submitSpinner.classList.add('hidden');
                submitBtn.disabled = false;
                setTimeout(() => {
                    submitText.textContent = 'Send Message';
                }, 3000);
            });
        });
        
        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const icon = question.querySelector('i');
                
                // Toggle answer visibility
                answer.classList.toggle('hidden');
                
                // Rotate icon
                if (answer.classList.contains('hidden')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(180deg)';
                }
                
                // Close other open answers
                document.querySelectorAll('.faq-answer').forEach(otherAnswer => {
                    if (otherAnswer !== answer && !otherAnswer.classList.contains('hidden')) {
                        otherAnswer.classList.add('hidden');
                        const otherIcon = otherAnswer.previousElementSibling.querySelector('i');
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                });
            });
        });
        
        // Service Carousel
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const track = document.getElementById('serviceCarousel');
        const slideCount = slides.length;
        
        function updateCarousel() {
            const slideWidth = slides[0].offsetWidth;
            track.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
        }
        
        document.querySelector('.carousel-next').addEventListener('click', () => {
            if (currentSlide < slideCount - 1) {
                currentSlide++;
            } else {
                currentSlide = 0;
            }
            updateCarousel();
        });
        
        document.querySelector('.carousel-prev').addEventListener('click', () => {
            if (currentSlide > 0) {
                currentSlide--;
            } else {
                currentSlide = slideCount - 1;
            }
            updateCarousel();
        });
        
        // Make carousel responsive
        window.addEventListener('resize', updateCarousel);
        
        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('.counter');
            const speed = 200;
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounters, 1);
                } else {
                    counter.innerText = target;
                }
            });
        }
        
        // Start counter animation when section is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        const counterSection = document.querySelector('#home');
        if (counterSection) {
            observer.observe(counterSection);
        }
        
        // Sticky header on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('shadow-md', 'py-2');
            } else {
                header.classList.remove('shadow-md', 'py-2');
            }
        });
    </script>
    
</body>
</html>