// DOM Elements
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-link');
const portfolioFilters = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');
const contactForm = document.getElementById('contactForm');

// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    // Toggle mobile menu
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }
});

// ✅ Smooth scrolling for section links only
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        const targetId = link.getAttribute('href');

        // Only handle anchors like #about, #contact
        if (targetId.startsWith("#")) {
            e.preventDefault();
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
        // else -> normal page redirect (login.php etc.)
    });
});

// Portfolio Filter Functionality
portfolioFilters.forEach(filter => {
    filter.addEventListener('click', () => {
        portfolioFilters.forEach(f => f.classList.remove('active'));
        filter.classList.add('active');
        
        const filterValue = filter.getAttribute('data-filter');
        
        portfolioItems.forEach(item => {
            if (filterValue === 'all') {
                item.style.display = 'block';
                item.classList.remove('hidden');
            } else {
                const itemCategory = item.getAttribute('data-category');
                if (itemCategory && itemCategory.includes(filterValue)) {
                    item.style.display = 'block';
                    item.classList.remove('hidden');
                } else {
                    item.style.display = 'none';
                    item.classList.add('hidden');
                }
            }
        });
        
        portfolioItems.forEach((item, index) => {
            if (!item.classList.contains('hidden')) {
                setTimeout(() => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            }
        });
    });
});

// Contact Form Handling
if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(contactForm);
        const submitBtn = contactForm.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;

        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;

        fetch('contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            contactForm.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        })
        .catch(() => {
            alert('There was an error sending your message. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Navbar background change on scroll
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = 'none';
    }
});

// ✅ Active navigation link highlighting (sections only)
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    const scrollPos = window.scrollY + 100;
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
            navLinks.forEach(link => link.classList.remove('active'));
            const activeLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
    });
});
// Animate elements on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animateElements = document.querySelectorAll('.service-card, .portfolio-item, .experience-card, .testimonial-card, .blog-card, .stat-item');
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
});

// Stats Counter Animation
const statsNumbers = document.querySelectorAll('.stat-number');

const animateStats = () => {
    statsNumbers.forEach(stat => {
        const target = parseInt(stat.textContent.replace('+', ''));
        const increment = target / 50;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : '');
        }, 40);
    });
};

// Trigger stats animation when stats section is visible
const statsSection = document.querySelector('.stats');
if (statsSection) {
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    statsObserver.observe(statsSection);
}

// Typewriter effect for hero title
const typewriterText = document.querySelector('.hero-name');
if (typewriterText) {
    const text = typewriterText.textContent;
    typewriterText.textContent = '';
    
    let i = 0;
    const typeWriter = () => {
        if (i < text.length) {
            typewriterText.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    };
    
    // Start typewriter effect after a delay
    setTimeout(typeWriter, 1000);
}

// Add mobile styles for navigation
const style = document.createElement('style');
style.textContent = `
    @media (max-width: 768px) {
        .nav-menu {
            position: fixed;
            left: -100%;
            top: 70px;
            flex-direction: column;
            background-color: white;
            width: 100%;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
            padding: 2rem 0;
        }
        
        .nav-menu.active {
            left: 0;
        }
        
        .nav-menu li {
            margin: 1rem 0;
        }
        
        .nav-link.active {
            color: #00D4AA;
        }
    }
`;
document.head.appendChild(style);

// Smooth reveal animations for sections
const revealSections = () => {
    const sections = document.querySelectorAll('section');
    
    sections.forEach(section => {
        const sectionTop = section.getBoundingClientRect().top;
        const triggerBottom = window.innerHeight * 0.8;
        
        if (sectionTop < triggerBottom) {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }
    });
};

// Initial setup for section animations
document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('section');
    
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'all 0.8s ease-out';
    });
    
    // Reveal hero section immediately
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        heroSection.style.opacity = '1';
        heroSection.style.transform = 'translateY(0)';
    }
});

window.addEventListener('scroll', revealSections);
window.addEventListener('load', revealSections);

// Preloader (optional)
window.addEventListener('load', () => {
    document.body.classList.add('loaded');
});

// Add CSS for preloader
const preloaderStyle = document.createElement('style');
preloaderStyle.textContent = `
    body:not(.loaded) {
        overflow: hidden;
    }
    
    body:not(.loaded)::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    body:not(.loaded)::after {
        content: 'Loading...';
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        color: #00D4AA;
        z-index: 10000;
    }
`;
document.head.appendChild(preloaderStyle);

// Circular Progress Animation
function animateCircularProgress() {
    const progressCircles = document.querySelectorAll('.circular-progress');
    
    progressCircles.forEach(circle => {
        const percentage = circle.getAttribute('data-percentage');
        const progressElement = circle.querySelector('.circle-progress');
        
        if (percentage && progressElement) {
            const circumference = 2 * Math.PI * 44; // radius is about 44px after border
            const strokeDasharray = circumference;
            const strokeDashoffset = circumference - (percentage / 100) * circumference;
            
            // Create SVG circle for better animation
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '100');
            svg.setAttribute('height', '100');
            svg.style.position = 'absolute';
            svg.style.top = '0';
            svg.style.left = '0';
            svg.style.transform = 'rotate(-90deg)';
            
            const circleProgress = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circleProgress.setAttribute('cx', '50');
            circleProgress.setAttribute('cy', '50');
            circleProgress.setAttribute('r', '44');
            circleProgress.setAttribute('fill', 'none');
            circleProgress.setAttribute('stroke', '#00D4AA');
            circleProgress.setAttribute('stroke-width', '6');
            circleProgress.setAttribute('stroke-linecap', 'round');
            circleProgress.setAttribute('stroke-dasharray', strokeDasharray);
            circleProgress.setAttribute('stroke-dashoffset', circumference);
            circleProgress.style.transition = 'stroke-dashoffset 2s ease-in-out';
            
            svg.appendChild(circleProgress);
            circle.appendChild(svg);
            
            // Animate when in view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            circleProgress.style.strokeDashoffset = strokeDashoffset;
                        }, 200);
                    }
                });
            });
            
            observer.observe(circle);
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', animateCircularProgress);

// Location Functions
function showAddLocationForm() {
    document.getElementById('add-location-form').style.display = 'block';
}

function hideAddLocationForm() {
    document.getElementById('add-location-form').style.display = 'none';
}

function editLocation(id) {
    fetch('admin.php?action=get_location&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            document.getElementById('edit_location_id').value = data.id;
            document.getElementById('edit_location_latitude').value = data.latitude;
            document.getElementById('edit_location_longitude').value = data.longitude;
            document.getElementById('edit-location-modal').style.display = 'block';
        })
        .catch(error => alert('Error loading location data'));
}

function closeEditLocationModal() {
    document.getElementById('edit-location-modal').style.display = 'none';
}

// Project Modal Functions
function openProjectModal(projectId) {
    fetch('admin.php?action=get_project&id=' + projectId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error loading project details');
                return;
            }
            const technologies = data.technologies ? data.technologies.split(',').map(tech => tech.trim()) : [];
            const techTags = technologies.map(tech => `<span class="tech-tag">${tech}</span>`).join('');
            document.getElementById('modal-project-content').innerHTML = `
                <div class="modal-header">
                    ${data.image ? `<img src="${data.image}" alt="${data.title}">` : `<div style="background:#00D4AA; height:100%; display:flex; align-items:center; justify-content:center; color:white; font-size:2rem;"><i class="fa fa-image"></i></div>`}
                    <div class="modal-header-overlay">
                        <h2>${data.title}</h2>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="modal-section">
                        <h3><i class="fa fa-info-circle"></i> Project Overview</h3>
                        <p>${data.detailed_description || data.short_description}</p>
                    </div>
                    ${technologies.length > 0 ? `
                    <div class="modal-section">
                        <h3><i class="fa fa-code"></i> Technologies Used</h3>
                        <div class="technologies-list">
                            ${techTags}
                        </div>
                    </div>
                    ` : ''}
                    <div class="modal-links">
                        ${data.github_link ? `<a href="${data.github_link}" target="_blank" class="modal-btn btn-github"><i class="fa fa-github"></i> View Code</a>` : ''}
                        ${data.demo_link ? `<a href="${data.demo_link}" target="_blank" class="modal-btn btn-demo"><i class="fa fa-external-link"></i> Live Demo</a>` : ''}
                    </div>
                </div>
            `;
            document.getElementById('project-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading project details');
        });
}

function closeProjectModal() {
    document.getElementById('project-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('project-modal');
    if (event.target === modal) {
        closeProjectModal();
    }
}

// Portfolio Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');
            
            const filter = btn.getAttribute('data-filter');
            
            portfolioItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});
