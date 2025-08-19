<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - St. Peter Hospital</title>
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
            min-height: 60vh;
            background: linear-gradient(120deg, #f9fcff, #e3f7ff);
            text-align: center;
            animation: bgShift 10s infinite alternate ease-in-out;
        }
        
        @keyframes bgShift {
            0% { background-position: left; }
            100% { background-position: right; }
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
            margin: 3rem 0;
            color: #0077b6;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .service-card {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: 0.4s ease;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .service-card.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 119, 182, 0.2);
        }
        
        .service-icon {
            font-size: 3rem;
            color: #0077b6;
            margin-bottom: 20px;
        }
        
        .service-card h3 {
            color: #0077b6;
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        
        .service-card p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            background: #0077b6;
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 119, 182, 0.4);
        }
        
        .btn:hover {
            background: #005f8a;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 95, 138, 0.5);
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
            
            .services-grid {
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
                <li><a href="<?= site_url('services') ?>" class="active">Services</a></li>
                <li><a href="<?= site_url('doctors') ?>">Doctors</a></li>
                <li><a href="<?= site_url('contact') ?>">Contact</a></li>
                <li><a href="<?= site_url('login') ?>">Login</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="hero">
        <h1>Our Services</h1>
        <p>Comprehensive healthcare services for you and your family</p>
    </section>
    
    <div class="container">
        <h2 class="section-title">Our Healthcare Services</h2>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>Cardiology</h3>
                <p>Comprehensive heart care including diagnostic tests, treatments, and preventive cardiology services.</p>
                <a href="#" class="btn">Learn More</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-bone"></i>
                </div>
                <h3>Orthopedics</h3>
                <p>Expert care for bones, joints, ligaments, tendons, and muscles with advanced treatment options.</p>
                <a href="#" class="btn">Learn More</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-baby"></i>
                </div>
                <h3>Pediatrics</h3>
                <p>Compassionate healthcare for infants, children, and adolescents from birth through young adulthood.</p>
                <a href="#" class="btn">Learn More</a>
            </div>
        </div>
    </div>
    
    <script>
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
            const cards = document.querySelectorAll('.service-card');
            
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
</body>
</html>
