<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Verify Your Email - HanapBuh.AI</title>
  <meta name="description"
    content="Verify your email address with OTP code to complete your registration on HanapBuh.AI.">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#1193d4",
            "background-light": "#f6f7f8",
            "background-dark": "#101c22",
          },
          fontFamily: { display: ["Poppins", "sans-serif"] },
          borderRadius: { xl: "0.75rem" },
        },
      },
    }
  </script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fade-in {
      animation: fadeIn 0.5s ease-out;
    }

    .input-error {
      border-color: #ef4444 !important;
    }

    .input-error:focus {
      ring-color: #ef4444 !important;
    }
  </style>
</head>

<body
  class="bg-gradient-to-br from-gray-50 to-gray-100 font-display min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8 fade-in">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
      <!-- Logo/Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          @include('partials.logo', ['class' => 'h-10 sm:h-12 w-auto'])
        </div>
        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
          Verify Your Email
        </h2>
        <p class="text-gray-600 text-sm">
          We've sent a 6-digit OTP code to
        </p>
        <p class="text-gray-900 font-semibold mt-1">
          {{ $email ?? 'your email address' }}
        </p>
      </div>

      <!-- Status Messages -->
      @if(session('status'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
          <p class="text-sm text-green-800 font-medium flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            {{ session('status') }}
          </p>
        </div>
      @endif

      <!-- OTP Display (for development/testing) -->
      @if(session('otp_display'))
        <div class="mb-6 p-4 bg-yellow-50 border-2 border-yellow-400 rounded-xl">
          <p class="text-sm text-yellow-900 font-bold mb-2 flex items-center">
            <span class="material-symbols-outlined mr-2">warning</span>
            Development Mode - OTP Code:
          </p>
          <div class="bg-white border-2 border-yellow-400 rounded-lg p-4 text-center">
            <p class="text-3xl font-bold text-yellow-900 tracking-widest">{{ session('otp_display') }}</p>
          </div>
          <p class="text-xs text-yellow-800 mt-2">
            @if(session('warning'))
              {{ session('warning') }}
            @elseif(session('error'))
              {{ session('error') }}
            @endif
          </p>
        </div>
      @endif

      @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
          <p class="text-sm text-red-800 font-semibold mb-2">Verification Error:</p>
          <ul class="text-sm text-red-700 list-disc list-inside">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- OTP Form -->
      <form action="{{ route('verify.email.verify') }}" method="POST" class="space-y-6" id="otp-form">
        @csrf

        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

        <!-- OTP Input -->
        <div>
          <label for="otp" class="block text-sm font-semibold text-gray-700 mb-2">
            Enter OTP Code
          </label>
          <input type="text" name="otp" id="otp" value="{{ old('otp') }}" maxlength="6" pattern="[0-9]{6}" required
            autocomplete="one-time-code" autofocus
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] text-center text-2xl font-bold tracking-widest @error('otp') input-error @enderror"
            placeholder="000000" inputmode="numeric" />
          @error('otp')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
          @enderror
          <p class="mt-2 text-xs text-gray-500">
            The code will expire in 15 minutes
          </p>
        </div>

        <!-- Submit Button -->
        <button type="submit"
          class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-[#1193d4] hover:bg-[#0f83bd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1193d4] transition-colors">
          Verify Email
        </button>
      </form>

      <!-- Resend OTP -->
      <div class="mt-6 text-center">
        <form action="{{ route('verify.email.resend') }}" method="POST" id="resend-form">
          @csrf
          <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
          <p class="text-sm text-gray-600 mb-3">
            Didn't receive the code?
          </p>
          <button type="submit" class="text-sm font-semibold text-[#1193d4] hover:text-[#0f83bd] transition-colors">
            Resend OTP
          </button>
        </form>
      </div>

      <!-- Back to Login -->
      <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
          ← Back to Login
        </a>
      </div>
    </div>

    <!-- Help Text -->
    <div class="text-center">
      <p class="text-xs text-gray-500">
        Check your spam folder if you don't see the email
      </p>
    </div>
  </div>

  <!-- Auto-focus and auto-submit script -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const otpInput = document.getElementById('otp');
      const otpForm = document.getElementById('otp-form');
      const submitButton = otpForm.querySelector('button[type="submit"]');

      // Auto-focus on OTP input
      otpInput.focus();

      // Auto-submit when 6 digits are entered
      otpInput.addEventListener('input', function (e) {
        // Remove non-numeric characters
        e.target.value = e.target.value.replace(/\D/g, '');

        // Auto-submit when 6 digits are entered
        if (e.target.value.length === 6) {
          // Show loading state
          submitButton.disabled = true;
          submitButton.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Verifying...';

          setTimeout(() => {
            otpForm.submit();
          }, 300);
        }
      });

      // Prevent form submission if OTP is not 6 digits
      otpForm.addEventListener('submit', function (e) {
        if (otpInput.value.length !== 6) {
          e.preventDefault();
          alert('Please enter a 6-digit OTP code');
          otpInput.focus();
          return;
        }

        // Show loading state on manual submit
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Verifying...';
      });
    });
  </script>
</body>

</html>