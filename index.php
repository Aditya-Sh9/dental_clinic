
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toothly Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(219, 234, 254, 0.5);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.7);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 99, 235, 0.9);
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
    </style>
    
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-tooth text-blue-500 text-2xl"></i>
                <h1 class="text-xl font-bold text-blue-800">Toothly</h1>
            </div>
            <nav>
                <ul class="flex items-center space-x-6">
                    <li><a href="#home" class="text-blue-600 hover:text-blue-800 font-medium">Home</a></li>
                    <li><a href="#services" class="text-gray-600 hover:text-blue-800">Services</a></li>
                    <li><a href="#about" class="text-gray-600 hover:text-blue-800">About Us</a></li>
                    <li><a href="#contact" class="text-gray-600 hover:text-blue-800">Contact</a></li>
                    <li class="ml-4">
                        <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                            <i class="fas fa-user mr-1"></i> Login/Signup
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="container mx-auto px-4 py-12" id="home">
        <div class="flex flex-col lg:flex-row items-center">
            <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-10">
                <h1 class="text-4xl md:text-5xl font-bold text-blue-900 leading-tight mb-6">
                    Your <span class="text-blue-600">Healthy Smile</span> Is Our Priority
                </h1>
                <p class="text-lg text-gray-700 mb-8">
                    Professional dental care with a gentle touch. Our experienced team provides comprehensive dental services in a comfortable environment.
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300 text-center">
                        <i class="fas fa-sign-in-alt mr-2"></i> Staff Login
                    </a>
                    <a href="#contact" class="border border-blue-600 text-blue-600 hover:bg-blue-50 font-semibold py-3 px-6 rounded-lg transition duration-300 text-center">
                        <i class="fas fa-phone-alt mr-2"></i> Call Us
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2">
                <div class="bg-white p-2 rounded-xl shadow-xl">
                    <img src="dentist.avif" 
                         alt="Dental Clinic" 
                         class="w-full h-auto rounded-lg">
                </div>
            </div>
        </div>
    </main>

    <!-- Services Section -->
    <section class="py-16 bg-white" id="services">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-800 mb-12">Our Services</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-blue-50 p-6 rounded-xl shadow-md hover:shadow-lg transition">
                    <div class="bg-blue-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-tooth text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-800 mb-2">General Dentistry</h3>
                    <p class="text-gray-700">Regular checkups, cleanings, and fillings to maintain your oral health.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-xl shadow-md hover:shadow-lg transition">
                    <div class="bg-blue-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-teeth text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-800 mb-2">Cosmetic Dentistry</h3>
                    <p class="text-gray-700">Teeth whitening, veneers, and smile makeovers to enhance your appearance.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-xl shadow-md hover:shadow-lg transition">
                    <div class="bg-blue-100 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-teeth-open text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-800 mb-2">Orthodontics</h3>
                    <p class="text-gray-700">Braces and aligners to straighten teeth and correct bites.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-16 bg-blue-50" id="about">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-10">
                    <h2 class="text-3xl font-bold text-blue-800 mb-6">About Toothly</h2>
                    <p class="text-gray-700 mb-4">
                        Founded in 2010, Toothly has been providing exceptional dental care to our community for over a decade. Our mission is to deliver personalized, high-quality dental services in a comfortable and welcoming environment.
                    </p>
                    <p class="text-gray-700">
                        Our team of experienced dentists and hygienists are committed to staying at the forefront of dental technology and techniques to ensure you receive the best possible care.
                    </p>
                </div>
                <div class="md:w-1/2">
                    <div class="bg-white p-2 rounded-xl shadow-xl">
                        <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                             alt="Dental Team" 
                             class="w-full h-auto rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-blue-800 text-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose Our Clinic?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-blue-700 rounded-lg shadow-md">
                    <i class="fas fa-user-md text-4xl mb-4 text-blue-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Expert Dentists</h3>
                    <p class="text-blue-100">Our team of certified professionals provides the highest quality dental care.</p>
                </div>
                <div class="text-center p-6 bg-blue-700 rounded-lg shadow-md">
                    <i class="fas fa-clock text-4xl mb-4 text-blue-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Flexible Hours</h3>
                    <p class="text-blue-100">We offer convenient appointment times to fit your busy schedule.</p>
                </div>
                <div class="text-center p-6 bg-blue-700 rounded-lg shadow-md">
                    <i class="fas fa-shield-alt text-4xl mb-4 text-blue-300"></i>
                    <h3 class="text-xl font-semibold mb-3">Modern Equipment</h3>
                    <p class="text-blue-100">State-of-the-art technology for precise diagnosis and comfortable treatment.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 bg-white" id="contact">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-800 mb-12">Contact Us</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-semibold text-blue-800 mb-4">Visit Our Clinic</h3>
                    <p class="text-gray-700 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i> 123 Dental Street, Smile City, SC 12345
                    </p>
                    <p class="text-gray-700 mb-4">
                        <i class="fas fa-phone-alt text-blue-600 mr-2"></i> (123) 456-7890
                    </p>
                    <p class="text-gray-700 mb-4">
                        <i class="fas fa-envelope text-blue-600 mr-2"></i> info@toothly.com
                    </p>
                    <p class="text-gray-700">
                        <i class="fas fa-clock text-blue-600 mr-2"></i> Mon-Fri: 8am-6pm, Sat: 9am-2pm
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-blue-800 mb-4">Send Us a Message</h3>
                    <form id="contactForm" class="space-y-4">
                        <div>
                            <input type="text" name="name" placeholder="Your Name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Your Email" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <input type="tel" name="phone" placeholder="Your Phone Number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <textarea name="message" placeholder="Your Message" rows="4" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow transition flex items-center justify-center">
                            <span id="submitText">Send Message</span>
                            <svg id="submitSpinner" class="hidden ml-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                       
                        <div id="formMessage" class="hidden p-3 rounded-lg"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-tooth mr-2"></i> Toothly
                    </h2>
                    <p class="mt-2 text-blue-200">Quality care for your smile.</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-blue-300 hover:text-white text-xl">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="text-blue-300 hover:text-white text-xl">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-blue-300 hover:text-white text-xl">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-8 text-center text-blue-300">
                <p>&copy; 2025 Toothly Clinic. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Contact Form Submission
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
            fetch('process_contact.php', {
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

                // Hide message after 5 seconds
                setTimeout(() => {
                    formMessage.classList.add('hidden');
                }, 5000);
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
    </script>
</body>
</html>