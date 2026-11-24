/**
 * Organization Inventory - Landing Page JavaScript
 * Handles Sticky Navbar behaviors and Floating Back to Top Button
 */

document.addEventListener('DOMContentLoaded', () => {
  const backToTopBtn = document.getElementById('backToTopBtn');
  const stickyNavbar = document.querySelector('.sticky-navbar');

  // Show/Hide Back to Top button on scroll
  const handleScroll = () => {
    const scrollPos = window.scrollY || document.documentElement.scrollTop;

    if (scrollPos > 120) {
      backToTopBtn?.classList.add('is-visible');
    } else {
      backToTopBtn?.classList.remove('is-visible');
    }

    // Enhance navbar shadow on deeper scroll
    if (scrollPos > 40) {
      stickyNavbar?.classList.add('scrolled');
    } else {
      stickyNavbar?.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });

  // Smooth scroll to top when button clicked
  backToTopBtn?.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
});
