{{-- resources/views/partials/footer.blade.php --}}
<footer class="bg-gray-900 text-white py-12 w-full">
  <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
    <div>
      <div class="flex items-center gap-2 mb-2">
        @include('partials.logo', ['class' => 'h-11 w-auto'])
        <h1 class="text-xl font-semibold">HanapBuh.AI</h1>
      </div>
      <p class="text-sm opacity-70 mt-2">
        Your AI-powered job-matching platform. Connecting talent with opportunities.
      </p>
    </div>

    <div>
      <h2 class="text-lg font-medium mb-3">Quick Links</h2>
      <ul class="space-y-2 text-sm">
        <li><a href="#" class="hover:text-primary transition" data-modal-open data-modal-title="About Us" data-modal-content="HanapBuh.AI is your AI-powered job-matching platform. We connect talent with opportunities in a smarter, faster way.">About Us</a></li>
        <li><a href="#" class="hover:text-primary transition" data-modal-open data-modal-title="Privacy Policy" data-modal-content="We value your privacy and are committed to protecting your personal information. This Privacy Policy outlines how we collect, use, and safeguard your data.

1. Information Collection: We collect data you provide during account registration, job applications, or newsletter sign-up.
2. Use of Information: Your information helps us connect you with relevant job opportunities and improve our services.
3. Third-Party Sharing: We do not sell your data. We only share information with trusted partners when necessary for service delivery.
4. Data Security: We implement robust measures to ensure your data is safe and protected against unauthorized access.

For any questions regarding your data, please contact our support team.">Privacy Policy</a></li>
        <li><a href="#" class="hover:text-primary transition" data-modal-open data-modal-title="Terms of Service" data-modal-content="Welcome to HanapBuh.AI. By accessing our platform, you agree to comply with the following terms:

1. Use of Service: You may use our platform solely for job search and application purposes.
2. Account Responsibility: You are responsible for maintaining the confidentiality of your login credentials.
3. Prohibited Activities: Any misuse, fraud, or attempt to breach our system will result in immediate suspension.
4. Changes to Terms: We may update these terms at any time without prior notice.

Continued use signifies your acceptance of these terms.">Terms of Service</a></li>
        <li><a href="#" class="hover:text-primary transition" data-modal-open data-modal-title="Contact" data-modal-content="We'd love to hear from you! Here's how you can reach us:

📧 Email: support@hanapbuh.ai
📞 Phone: +63 912 345 6789
📍 Address: 123 AI Street, Quezon City, Metro Manila, Philippines

Feel free to contact us with any questions, feedback, or support needs. Our team is here to help!">Contact</a></li>
      </ul>
    </div>

    <div>
      <h2 class="text-lg font-medium mb-3">Stay Updated</h2>
      <p class="text-sm opacity-70">Subscribe to our newsletter for the latest job trends.</p>

      <form class="mt-3 flex" action="#" method="post" onsubmit="return false;">
        @csrf
        <input
          type="email"
          placeholder="Enter your email"
          class="w-full p-2 rounded-l bg-gray-800 text-white border-none focus:ring-2 focus:ring-primary placeholder:text-gray-400"
        />
        <button class="bg-primary px-5 py-3 rounded-r text-white font-semibold hover:opacity-90 transition-opacity">
          Subscribe
        </button>
      </form>

      <div class="flex space-x-4 mt-4">
        <a href="https://www.facebook.com/people/HanapBuhAI/61576296988320/" class="text-gray-400 hover:text-primary transition" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
          <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M13 22v-8h2.5l.5-3H13V9.5c0-.8.2-1.3 1.4-1.3H16V5.3C15.4 5.2 14.5 5 13.5 5 11.4 5 10 6.2 10 8.8V11H7.5v3H10v8h3z" /></svg>
        </a>
        <a href="https://x.com/hanapbuh_ai" class="text-gray-400 hover:text-primary transition" target="_blank" rel="noopener noreferrer" aria-label="X / Twitter">
          <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M18.244 3H21l-6.5 7.43L22.5 21h-5.27l-4.12-5.21L7.5 21H3l7.02-8.02L2.25 3h5.36l3.79 4.93L18.244 3z" /></svg>
        </a>
        <a href="https://www.instagram.com/hanapbuh.ai/" class="text-gray-400 hover:text-primary transition" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
          <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 5a5 5 0 100 10 5 5 0 000-10zm6.5-.75a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
        </a>
      </div>
    </div>
  </div>

  <div class="mt-8 border-t border-gray-700 pt-4 text-center text-sm opacity-70">
    &copy; {{ date('Y') }} HanapBuh.AI. All Rights Reserved.
  </div>
</footer>

{{-- Modal (overlay) --}}
<div id="footer-modal"
     class="hidden fixed inset-0 bg-black/50 flex justify-center items-center z-50 p-4">
  <div class="bg-white rounded-lg p-6 w-[90%] max-w-2xl max-h-[80vh] overflow-hidden flex flex-col relative text-black">
    <button
      type="button"
      class="absolute top-2 right-2 text-xl text-gray-500 hover:text-gray-700"
      data-modal-close
      aria-label="Close modal">×</button>
    <h2 id="footer-modal-title" class="text-xl font-bold mb-4 text-center"></h2>
    <div id="footer-modal-content"
         class="text-sm whitespace-pre-line overflow-y-auto flex-grow pr-1"
         style="max-height: calc(80vh - 100px)"></div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const modal = document.getElementById('footer-modal');
  const titleEl = document.getElementById('footer-modal-title');
  const contentEl = document.getElementById('footer-modal-content');

  function openModal(title, content) {
    titleEl.textContent = title || '';
    contentEl.textContent = content || '';
    modal.classList.remove('hidden');
  }
  function closeModal() {
    modal.classList.add('hidden');
    titleEl.textContent = '';
    contentEl.textContent = '';
  }

  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-modal-open]');
    if (trigger) {
      e.preventDefault();
      openModal(
        trigger.getAttribute('data-modal-title'),
        trigger.getAttribute('data-modal-content')
      );
    }
  });

  modal?.addEventListener('click', function (e) {
    if (e.target === modal || e.target.matches('[data-modal-close]')) {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      closeModal();
    }
  });
})();
</script>
@endpush
