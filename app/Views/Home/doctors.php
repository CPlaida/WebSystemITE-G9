<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - St. Peter Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(120deg, #f0f8ff, #e6f7ff);
            overflow-x: hidden;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.4);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1000;
            transition: 0.3s ease;
        }
        header.scrolled {
            background: rgba(0, 119, 182, 0.85);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0077b6;
            text-decoration: none;
        }
        header.scrolled .logo {
            color: white;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
        }
        header.scrolled nav ul li a {
            color: #fff;
        }
        
        nav ul li a:hover, nav ul li a.active {
            color: #ffdd57;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 200px 50px 100px;
            min-height: 100vh;
            background: linear-gradient(120deg, #f9fcff, #e3f7ff);
            text-align: center;
            animation: bgShift 10s infinite alternate ease-in-out;
        }
        @keyframes bgShift {
            0% { background-position: left; }
            100% { background-position: right; }
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 50px;
            animation: fadeInUp 1s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            color: #0077b6;
        }
        
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .doctor-card {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: 0.4s ease;
            transform: translateY(20px);
            opacity: 0;
        }
        .doctor-card.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .doctor-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 119, 182, 0.3);
        }
        
        .doctor-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-bottom: 3px solid #0077b6;
        }
        
        .doctor-info {
            padding: 25px;
            text-align: center;
        }
        
        .doctor-name {
            color: #0077b6;
            margin-bottom: 10px;
            font-size: 1.4em;
        }
        
        .doctor-specialty {
            color: #555;
            font-style: italic;
            margin-bottom: 15px;
            display: block;
            font-size: 0.9em;
        }
        
        .doctor-contact {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            color: #555;
            font-size: 0.9em;
        }
        
        .contact-item i {
            margin-right: 10px;
            color: #0077b6;
            width: 20px;
            text-align: center;
            font-size: 1.1em;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            nav ul {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .hero {
                padding: 180px 20px 80px;
                min-height: auto;
            }
            .container {
                padding: 40px 20px;
            }
            .doctors-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header id="navbar">
    <div class="logo">St. Peter Hospital Inc.</div>
    <nav>
        <ul>
            <li><a href="<?= site_url('/') ?>">Home</a></li>
            <li><a href="<?= site_url('services') ?>">Services</a></li>
            <li><a href="<?= site_url('doctors') ?>" class="active">Doctors</a></li>
            <li><a href="<?= site_url('login') ?>">Login</a></li>
        </ul>
    </nav>
</header>
    
    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Animate cards on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.doctor-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
    
    <section class="hero">
        <div>
            <h1>Our Expert Doctors</h1>
            <p>Meet our team of experienced healthcare professionals</p>
        </div>
    </section>
    
    <div class="container">
        <h2 class="section-title">Our Specialist Doctors</h2>
        
        <div class="doctors-grid">
            <!-- Doctor 1 -->
            <div class="doctor-card">
                <img src="https://scontent.fdvo1-1.fna.fbcdn.net/v/t39.30808-1/405216790_3268226949987341_1066780437323176172_n.jpg?stp=dst-jpg_s200x200_tt6&_nc_cat=108&ccb=1-7&_nc_sid=e99d92&_nc_eui2=AeGt934weN5IBBYHDi6YAsA2zRXRBShBjALNFdEFKEGMAu8XBLKviKj5wx8LEcOIEEDd_cGLtxa20BHLR_AB-U3O&_nc_ohc=qjBd7sOeqroQ7kNvwEzo27b&_nc_oc=Adll-wTMFegs93Sae2ztm_DB43WM7Bbfmjn7R4B6aRGGHoWJ_OlWg2_3ecXaqm3FNuI&_nc_zt=24&_nc_ht=scontent.fdvo1-1.fna&_nc_gid=ickjFPqeR7yyB4dM80_z5A&oh=00_AfXK1emUZ8cQYF0B189FHWZ_2sRZj4_e18AFvu8kVUHVlQ&oe=68A5A63B" alt="Dr. John Smith" class="doctor-img">
                <div class="doctor-info">
                    <h3 class="doctor-name">Dr. Frenchie Labasa</h3>
                    <span class="doctor-specialty">Cardiologist</span>
                    <p>With over 15 years of experience in cardiology, Dr. Labasa specializes in interventional cardiology and heart failure management.</p>
                    <div class="doctor-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+63 912 345 6789</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>FrenchieLabas@stpeterhospital.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon-Fri: 9:00 AM - 5:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Doctor 2 -->
            <div class="doctor-card">
                <img src="https://scontent.fdvo1-1.fna.fbcdn.net/v/t39.30808-1/517121031_2090966228060468_8744428888213666402_n.jpg?stp=c0.11.1051.1049a_dst-jpg_s200x200_tt6&_nc_cat=111&ccb=1-7&_nc_sid=e99d92&_nc_eui2=AeEUDoS8moz3RuzJCGHTR3ZXAWU_CxReAUoBZT8LFF4BShngMg2hJTOVE2JAj0RYg-JHUQxH0JehB0sYveKRRuAt&_nc_ohc=83em7i1rkH0Q7kNvwH_WY0S&_nc_oc=AdnfvzN43XA2Kr1slcuYik7A9xii20TPGg-nkwzhHHQoYvwDZABICrQqCMmicBd57QM&_nc_zt=24&_nc_ht=scontent.fdvo1-1.fna&_nc_gid=HsqaEcQ4PUHyMQEkw-CSZg&oh=00_AfUZzbH2uPtnIu6lOUnu4EuoOd1c5rGbE2SKZPdjENdFyw&oe=68A5ACCA" alt="Dr. Cristine Plaida" class="doctor-img">
                <div class="doctor-info">
                    <h3 class="doctor-name">Dr. Cristine Plaida</h3>
                    <span class="doctor-specialty">Pediatrician</span>
                    <p>Dr. Plaida has been caring for children's health for over 12 years, with special focus on pediatric immunology and allergies.</p>
                    <div class="doctor-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+63 923 456 7890</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>Cristine.Plaida@stpeterhospital.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Tue-Sat: 10:00 AM - 6:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Doctor 3 -->
            <div class="doctor-card">
                <img src="https://scontent.fdvo1-2.fna.fbcdn.net/v/t39.30808-6/480167593_1427854074847775_6874637637756121660_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=a5f93a&_nc_eui2=AeG_akLHvI5wWby5pJz2Xxz8hprznXn2DH-GmvOdefYMfwxoLZwVonA2q85FQTcib0hzqyMi4Mf_kCZ7JZfcr7YO&_nc_ohc=ZnyeXnb810gQ7kNvwG2Lhtg&_nc_oc=AdlzWoB1Yex3mdRw-0VMUYjrPo8cEaj9_GxEExp1RFCebKcnQ0SLQzlcJ9Tm2M_aPnw&_nc_zt=23&_nc_ht=scontent.fdvo1-2.fna&_nc_gid=klV6bq-qjOe8SIu3zk2ZUw&oh=00_AfV2bntL2mWKIom3XFv6hxy0uiYGF2yMZhFg4TvxGKH8Aw&oe=68A58E5F" alt="Dr. Charles Agui" class="doctor-img">
                <div class="doctor-info">
                    <h3 class="doctor-name">Dr. Jerald Maca</h3>
                    <span class="doctor-specialty">Orthopedic Surgeon</span>
                    <p>Specializing in sports medicine and joint replacement, Dr. Agui brings 18 years of surgical expertise to our team.</p>
                    <div class="doctor-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+63 917 890 1234</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>Charles.Agui@stpeterhospital.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon, Wed, Fri: 8:00 AM - 4:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="contact-section">
            <h2 class="section-title">Contact Our Clinic</h2>
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div>
                        <h3 style="color: #0077b6; margin-bottom: 1rem;">Visit Us</h3>
                        <p>Ramon Magsaysay Memorial Colleges <br>South cotabato, G,S,C, Philippines 9500</p>
                    </div>
                    <div>
                        <h3 style="color: #0077b6; margin-bottom: 1rem;">Contact Info</h3>
                        <p><i class="fas fa-phone" style="color: #0077b6; margin-right: 10px;"></i> (63) 0978150583</p>
                        <p><i class="fas fa-envelope" style="color: #0077b6; margin-right: 10px;"></i> Rmmc@stpeterhospital.com</p>
                    </div>
                    <div>
                        <h3 style="color: #0077b6; margin-bottom: 1rem;">Clinic Hours</h3>
                        <p>Monday - Friday: 8:00 AM - 8:00 PM</p>
                        <p>Saturday: 9:00 AM - 5:00 PM</p>
                        <p>Sunday: Emergency Only</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <footer>
        <p>&copy; 2023 St. Peter Hospital. All Rights Reserved.</p>
    </footer>
</body>
</html>
