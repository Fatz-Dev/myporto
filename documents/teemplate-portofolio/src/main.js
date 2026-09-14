/**
 * Portfolio Vanilla JavaScript
 * Pure JavaScript without frameworks or libraries
 */

// Testimonials data for interactive slider
const testimonials = [
  {
    name: 'Kristin Watson',
    role: 'Fashion Designer',
    quote: '"My instructor was very professional and strict understanding. He helped understand health platforms adhere to strict healthcare empower regulations, ensuring the privacy and dignity of each patient."',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80'
  },
  {
    name: 'Dr. Arthur Pendelton',
    role: 'Clinical Director, St. Jude',
    quote: '"Working alongside FatzDev has been an inspiring experience. Her precision, diagnostic accuracy, and bedside empathy set the benchmark for modern clinical practice."',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80'
  },
  {
    name: 'Eleanor Vance',
    role: 'Managing Partner, Global Tech',
    quote: '"Dr. Nguyen transformed my chronic condition management. The digital monitoring, combined with her detailed weekly assessments, restored my daily vitality completely."',
    avatar: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=200&q=80'
  }
];

let currentTestimonialIndex = 0;

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Sidebar Toggle
  const sidebar = document.getElementById('main-sidebar');
  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  const openMenuBtn = document.getElementById('open-sidebar-btn');
  const closeMenuBtn = document.getElementById('close-sidebar-btn');

  function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    sidebarBackdrop.classList.remove('hidden');
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    sidebarBackdrop.classList.add('hidden');
  }

  if (openMenuBtn) openMenuBtn.addEventListener('click', openSidebar);
  if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeSidebar);
  if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);

  // 2. Navigation smooth scroll and active highlighting
  const navLinks = document.querySelectorAll('.nav-link');
  navLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = link.getAttribute('data-target');
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth' });
        closeSidebar();
        setActiveNav(targetId);
      }
    });
  });

  function setActiveNav(sectionId) {
    navLinks.forEach((link) => {
      const target = link.getAttribute('data-target');
      if (target === sectionId) {
        link.classList.remove('text-slate-400', 'hover:bg-slate-800/50');
        link.classList.add('bg-sky-500', 'text-white', 'font-bold');
      } else {
        link.classList.add('text-slate-400', 'hover:bg-slate-800/50');
        link.classList.remove('bg-sky-500', 'text-white', 'font-bold');
      }
    });
  }

  // 3. ScrollSpy using IntersectionObserver
  const sections = document.querySelectorAll('section[id]');
  const observerOptions = {
    root: null,
    rootMargin: '-20% 0px -60% 0px',
    threshold: 0
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        setActiveNav(entry.target.id);
      }
    });
  }, observerOptions);

  sections.forEach((section) => observer.observe(section));

  // 4. Testimonial Slider
  const quoteEl = document.getElementById('testimonial-quote');
  const nameEl = document.getElementById('testimonial-name');
  const roleEl = document.getElementById('testimonial-role');
  const avatarEl = document.getElementById('testimonial-avatar');
  const prevBtn = document.getElementById('testimonial-prev-btn');
  const nextBtn = document.getElementById('testimonial-next-btn');

  function updateTestimonial(index) {
    currentTestimonialIndex = index;
    const item = testimonials[currentTestimonialIndex];
    if (quoteEl) quoteEl.textContent = item.quote;
    if (nameEl) nameEl.textContent = item.name;
    if (roleEl) roleEl.textContent = item.role;
    if (avatarEl) {
      avatarEl.src = item.avatar;
      avatarEl.alt = item.name;
    }
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      const nextIndex = (currentTestimonialIndex - 1 + testimonials.length) % testimonials.length;
      updateTestimonial(nextIndex);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      const nextIndex = (currentTestimonialIndex + 1) % testimonials.length;
      updateTestimonial(nextIndex);
    });
  }

  // 5. Appointment Modal
  const appointmentModal = document.getElementById('appointment-modal');
  const openAppointmentBtns = document.querySelectorAll('.open-appointment-trigger');
  const closeAppointmentBtn = document.getElementById('close-appointment-btn');
  const appointmentBackdrop = document.getElementById('appointment-modal-backdrop');
  const appointmentForm = document.getElementById('appointment-form');
  const appointmentSuccess = document.getElementById('appointment-success');

  function openAppointment() {
    if (appointmentModal) {
      appointmentModal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
  }

  function closeAppointment() {
    if (appointmentModal) {
      appointmentModal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      if (appointmentSuccess) appointmentSuccess.classList.add('hidden');
      if (appointmentForm) appointmentForm.classList.remove('hidden');
    }
  }

  openAppointmentBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openAppointment();
    });
  });

  if (closeAppointmentBtn) closeAppointmentBtn.addEventListener('click', closeAppointment);
  if (appointmentBackdrop) appointmentBackdrop.addEventListener('click', closeAppointment);

  if (appointmentForm) {
    appointmentForm.addEventListener('submit', (e) => {
      e.preventDefault();
      appointmentForm.classList.add('hidden');
      if (appointmentSuccess) {
        appointmentSuccess.classList.remove('hidden');
      }
    });
  }

  // 6. Contact Form Handler
  const contactForm = document.getElementById('contact-form');
  const contactSuccess = document.getElementById('contact-success-msg');

  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      contactForm.reset();
      if (contactSuccess) {
        contactSuccess.classList.remove('hidden');
        setTimeout(() => {
          contactSuccess.classList.add('hidden');
        }, 5000);
      }
    });
  }
});
