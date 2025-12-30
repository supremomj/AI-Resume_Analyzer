@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<!-- Modal Wrapper -->
<div id="termsModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
  <div class="bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white rounded-xl p-8 max-w-3xl mx-auto overflow-y-auto max-h-[80vh] w-full">
    <!-- Modal Header -->
    <div class="text-center mb-6">
      <h2 class="text-3xl font-bold">Terms of Service</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400">Please read the following terms carefully before proceeding.</p>
    </div>

    <!-- Modal Body (content) -->
    <div class="max-h-[60vh] overflow-y-scroll mb-6">
      <h3 class="text-xl font-semibold mb-2">1. Introduction</h3>
      <p>These Terms of Service ("Terms") govern your access to and use of CareerMatch AI. By accessing or using the website, you agree to be bound by these Terms.</p>

      <h3 class="text-xl font-semibold mt-4 mb-2">2. User Responsibilities</h3>
      <p>As a user, you are responsible for maintaining the confidentiality of your login information and ensuring that all activities under your account comply with these Terms.</p>

      <!-- Add more sections as needed -->

      <h3 class="text-xl font-semibold mt-4 mb-2">3. Changes to Terms</h3>
      <p>We may update our Terms of Service from time to time. We will notify you of any changes by posting the new Terms on this page.</p>
    </div>

    <!-- Close Button -->
    <div class="flex justify-center">
      <button id="closeModal" class="bg-primary text-white px-6 py-2 rounded-full disabled:opacity-50" disabled>
        Accept & Close
      </button>
    </div>
  </div>
</div>

<!-- JavaScript to handle scrolling and button enable -->
<script>
  const closeModalButton = document.getElementById('closeModal');
  const termsModal = document.getElementById('termsModal');

  // Monitor scroll to enable the button when user has scrolled to the bottom
  termsModal.querySelector('.overflow-y-scroll').addEventListener('scroll', function() {
    const modalContent = this;
    if (modalContent.scrollTop + modalContent.clientHeight >= modalContent.scrollHeight) {
      closeModalButton.disabled = false;
    }
  });

  // Close the modal when the button is clicked
  closeModalButton.addEventListener('click', function() {
    termsModal.style.display = 'none';
  });
</script>
@endsection
