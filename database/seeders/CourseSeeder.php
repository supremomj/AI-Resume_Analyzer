<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Seed the courses table with real courses from multiple platforms.
     * Data sourced from the AI Resume Analyzer courses.csv dataset.
     */
    public function run(): void
    {
        $courses = [
            // ─── Data Science ───────────────────────────────────────────
            ['title' => 'Machine Learning Crash Course by Google', 'provider' => 'Google', 'url' => 'https://developers.google.com/machine-learning/crash-course', 'field' => 'Data Science', 'is_free' => true, 'description' => 'A self-study guide for aspiring machine learning practitioners. Covers ML fundamentals with TensorFlow.'],
            ['title' => 'Machine Learning A-Z', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/machinelearning/', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Learn to create machine learning algorithms in Python and R from two data science experts.', 'rating' => 4.5],
            ['title' => 'Machine Learning by Andrew NG', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/machine-learning', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Stanford University course covering supervised/unsupervised learning, best practices, and AI.', 'rating' => 4.9],
            ['title' => 'Data Science Foundations: Fundamentals', 'provider' => 'LinkedIn Learning', 'url' => 'https://www.linkedin.com/learning/data-science-foundations-fundamentals-5', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Explore the fundamentals of data science including statistics, machine learning, and data analysis.', 'rating' => 4.5],
            ['title' => 'Data Scientist with Python', 'provider' => 'DataCamp', 'url' => 'https://www.datacamp.com/tracks/data-scientist-with-python', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Career track covering Python, data manipulation, statistical analysis, and machine learning.', 'rating' => 4.6],
            ['title' => 'Programming for Data Science with Python', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/programming-for-data-science-nanodegree--nd104', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Learn Python, SQL, and Git to prepare for a data science career.', 'rating' => 4.5],
            ['title' => 'Introduction to Data Science', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/introduction-to-data-science--cd0017', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Covers the core data science workflow including data wrangling, analysis, and visualization.'],
            ['title' => 'Intro to Machine Learning with TensorFlow', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/intro-to-machine-learning-with-tensorflow-nanodegree--nd230', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Nanodegree to learn supervised, unsupervised, and deep learning with TensorFlow.', 'rating' => 4.5],
            ['title' => 'Data Science Bootcamp', 'provider' => 'Zuitt', 'url' => 'https://zuitt.co/courses/data-science-bootcamp/', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Philippine-based data science bootcamp covering Python, data analytics, and machine learning.'],
            ['title' => 'Data Analytics Course', 'provider' => 'Eskwelabs', 'url' => 'https://www.eskwelabs.com/data-analytics', 'field' => 'Data Science', 'is_free' => false, 'description' => 'Filipino data analytics sprint covering SQL, Python, and data storytelling.'],

            // ─── Web Development ────────────────────────────────────────
            ['title' => 'Django Crash Course', 'provider' => 'YouTube', 'url' => 'https://youtu.be/e1IyzVyrLSU', 'field' => 'Web Development', 'is_free' => true, 'description' => 'Free crash course on building web apps with Python Django framework.'],
            ['title' => 'Python and Django Full Stack Bootcamp', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/python-and-django-full-stack-web-developer-bootcamp', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Full stack web development with Python, Django, HTML, CSS and JavaScript.', 'rating' => 4.5],
            ['title' => 'React Crash Course', 'provider' => 'YouTube', 'url' => 'https://youtu.be/Dorf8i6lCuk', 'field' => 'Web Development', 'is_free' => true, 'description' => 'Free crash course on React.js for building modern user interfaces.'],
            ['title' => 'Full Stack Web Developer - MEAN Stack', 'provider' => 'Simplilearn', 'url' => 'https://www.simplilearn.com/full-stack-web-developer-mean-stack-certification-training', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Master MongoDB, Express.js, Angular and Node.js in this full stack certification.', 'rating' => 4.5],
            ['title' => 'Node.js and Express.js', 'provider' => 'YouTube', 'url' => 'https://youtu.be/Oe421EPjeBE', 'field' => 'Web Development', 'is_free' => true, 'description' => 'Free full course on building backend web applications with Node.js and Express.'],
            ['title' => 'Flask: Develop Web Applications in Python', 'provider' => 'Educative', 'url' => 'https://www.educative.io/courses/flask-develop-web-applications-in-python', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Learn to build web applications in Python using the Flask microframework.'],
            ['title' => 'Full Stack Web Developer Nanodegree', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/full-stack-web-developer-nanodegree--nd0044', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Nanodegree covering APIs, authentication, server deployment, and databases.', 'rating' => 4.6],
            ['title' => 'Full Stack Web Development Bootcamp', 'provider' => 'Zuitt', 'url' => 'https://zuitt.co/courses/full-stack-web-development/', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Philippine bootcamp covering HTML/CSS, JavaScript, MongoDB, Express, React, and Node.js.'],
            ['title' => 'Web Development Course', 'provider' => 'KodeGo', 'url' => 'https://kodego.ph/', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Filipino web development bootcamp with job placement assistance.'],
            ['title' => 'JavaScript Bootcamp', 'provider' => 'Avion School', 'url' => 'https://avionschool.com/', 'field' => 'Web Development', 'is_free' => false, 'description' => 'Philippine coding bootcamp focused on full-stack JavaScript development.'],

            // ─── Android Development ────────────────────────────────────
            ['title' => 'Android Development for Beginners', 'provider' => 'YouTube', 'url' => 'https://youtu.be/fis26HvvDII', 'field' => 'Android Development', 'is_free' => true, 'description' => 'Free beginner course on building Android apps.'],
            ['title' => 'Android App Development Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/android-app-development', 'field' => 'Android Development', 'is_free' => false, 'description' => 'Build complete Android apps and learn core Android development skills.', 'rating' => 4.5],
            ['title' => 'Become an Android Kotlin Developer', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/android-kotlin-developer-nanodegree--nd940', 'field' => 'Android Development', 'is_free' => false, 'description' => 'Learn Kotlin to build professional Android apps with modern architecture.', 'rating' => 4.6],
            ['title' => 'Android Basics by Google', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/android-basics-nanodegree-by-google--nd803', 'field' => 'Android Development', 'is_free' => false, 'description' => 'Google-designed nanodegree covering Android fundamentals for beginners.'],
            ['title' => 'The Complete Android Developer Course', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/complete-android-n-developer-course/', 'field' => 'Android Development', 'is_free' => false, 'description' => 'Build 23 apps including an Uber clone and Instagram clone.', 'rating' => 4.4],
            ['title' => 'Flutter & Dart Complete Course', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/flutter-dart-the-complete-flutter-app-development-course/', 'field' => 'Android Development', 'is_free' => false, 'description' => 'Build iOS and Android apps with a single Flutter codebase.', 'rating' => 4.6],
            ['title' => 'Flutter App Development Course', 'provider' => 'YouTube', 'url' => 'https://youtu.be/rZLR5olMR64', 'field' => 'Android Development', 'is_free' => true, 'description' => 'Free full course on building beautiful mobile apps with Flutter.'],

            // ─── iOS Development ────────────────────────────────────────
            ['title' => 'iOS App Development by LinkedIn', 'provider' => 'LinkedIn Learning', 'url' => 'https://www.linkedin.com/learning/subscription/topics/ios', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'Comprehensive iOS development learning path covering Swift and UIKit.'],
            ['title' => 'iOS & Swift Complete Bootcamp', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/ios-13-app-development-bootcamp/', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'The most comprehensive iOS development course with 55+ hours of content.', 'rating' => 4.8],
            ['title' => 'Become an iOS Developer', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/ios-developer-nanodegree--nd003', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'Nanodegree to master iOS development and build a portfolio of apps.', 'rating' => 4.6],
            ['title' => 'iOS App Development with Swift', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/app-development', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'University of Toronto specialization on iOS app development with Swift.', 'rating' => 4.4],
            ['title' => 'Mobile App Development with Swift', 'provider' => 'edX', 'url' => 'https://www.edx.org/professional-certificate/curtinx-mobile-app-development-with-swift', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'Professional certificate in mobile app development using Swift.'],
            ['title' => 'Learn Swift by Codecademy', 'provider' => 'Codecademy', 'url' => 'https://www.codecademy.com/learn/learn-swift', 'field' => 'IOS Development', 'is_free' => false, 'description' => 'Interactive Swift programming course for beginners.', 'rating' => 4.4],
            ['title' => 'Swift Tutorial - Full Course for Beginners', 'provider' => 'YouTube', 'url' => 'https://youtu.be/comQ1-x2a1Q', 'field' => 'IOS Development', 'is_free' => true, 'description' => 'Free comprehensive Swift tutorial covering all the fundamentals.'],

            // ─── UI/UX Development ──────────────────────────────────────
            ['title' => 'Google UX Design Professional Certificate', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/professional-certificates/google-ux-design', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Google-designed certificate covering UX research, wireframing, prototyping, and testing.', 'rating' => 4.8],
            ['title' => 'UI / UX Design Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/ui-ux-design', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'CalArts specialization on interface design, user experience, and web design.', 'rating' => 4.5],
            ['title' => 'Complete App Design Course - UX, UI and Design Thinking', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/the-complete-app-design-course-ux-and-ui-design/', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Learn UX/UI design from scratch using design thinking principles.', 'rating' => 4.5],
            ['title' => 'UX & Web Design Master Course', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/ux-web-design-master-course-strategy-design-development/', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Strategy, design, and development for web UX design.', 'rating' => 4.6],
            ['title' => 'DESIGN RULES: Principles + Practices for Great UI Design', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/design-rules/', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Master the principles and practices behind effective UI design.', 'rating' => 4.6],
            ['title' => 'Become a UX Designer Nanodegree', 'provider' => 'Udacity', 'url' => 'https://www.udacity.com/course/ux-designer-nanodegree--nd578', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Master the UX design process and build a professional portfolio.'],
            ['title' => 'Adobe XD Tutorial: User Experience Design', 'provider' => 'YouTube', 'url' => 'https://youtu.be/68w2VwalD5w', 'field' => 'UI-UX Development', 'is_free' => true, 'description' => 'Free course on designing user experiences with Adobe XD.'],
            ['title' => 'UI/UX Design Bootcamp', 'provider' => 'Zuitt', 'url' => 'https://zuitt.co/courses/ui-ux-design/', 'field' => 'UI-UX Development', 'is_free' => false, 'description' => 'Philippine bootcamp covering UI/UX fundamentals, Figma, and portfolio building.'],

            // ─── Software Engineering ────────────────────────────────────
            ['title' => 'CS50: Introduction to Computer Science', 'provider' => 'edX', 'url' => 'https://www.edx.org/learn/computer-science/harvard-university-cs50-s-introduction-to-computer-science', 'field' => 'Software Engineering', 'is_free' => true, 'description' => 'Harvard University\'s famous intro to CS covering algorithms, data structures, and software engineering.', 'rating' => 4.9],
            ['title' => 'Software Engineering Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/software-engineering', 'field' => 'Software Engineering', 'is_free' => false, 'description' => 'University of Minnesota specialization covering software design, architecture, and testing.', 'rating' => 4.5],
            ['title' => 'The Complete Python Bootcamp', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/complete-python-bootcamp/', 'field' => 'Software Engineering', 'is_free' => false, 'description' => 'Learn Python like a professional from basics to advanced topics.', 'rating' => 4.6],
            ['title' => 'Java Programming Masterclass', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/java-the-complete-java-developer-course/', 'field' => 'Software Engineering', 'is_free' => false, 'description' => 'Complete Java programming course for software developers.', 'rating' => 4.6],
            ['title' => 'Git & GitHub Crash Course', 'provider' => 'YouTube', 'url' => 'https://youtu.be/RGOj5yH7evk', 'field' => 'Software Engineering', 'is_free' => true, 'description' => 'Free course covering Git version control and GitHub collaboration.'],

            // ─── Business Administration ─────────────────────────────────
            ['title' => 'Business Foundations Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/wharton-business-foundations', 'field' => 'Business Administration', 'is_free' => false, 'description' => 'Wharton School specialization covering marketing, accounting, operations, and finance.', 'rating' => 4.7],
            ['title' => 'Business Strategy Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/business-strategy', 'field' => 'Business Administration', 'is_free' => false, 'description' => 'University of Virginia Darden School course on business strategy and competitive analysis.', 'rating' => 4.6],
            ['title' => 'Project Management Professional Certificate', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/professional-certificates/google-project-management', 'field' => 'Business Administration', 'is_free' => false, 'description' => 'Google-designed program covering Agile, Scrum, and project management fundamentals.', 'rating' => 4.8],

            // ─── Accounting and Finance ──────────────────────────────────
            ['title' => 'Finance & Quantitative Modeling', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/finance-quantitative-modeling-analysts', 'field' => 'Accounting and Finance', 'is_free' => false, 'description' => 'Wharton specialization on financial modeling, corporate finance, and quantitative analysis.', 'rating' => 4.6],
            ['title' => 'Financial Markets by Yale', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/financial-markets-global', 'field' => 'Accounting and Finance', 'is_free' => false, 'description' => 'Robert Shiller\'s legendary Yale course on financial markets and institutions.', 'rating' => 4.8],
            ['title' => 'Accounting Fundamentals', 'provider' => 'edX', 'url' => 'https://www.edx.org/learn/accounting', 'field' => 'Accounting and Finance', 'is_free' => false, 'description' => 'Learn the fundamentals of accounting, financial statements, and bookkeeping.'],

            // ─── Marketing ──────────────────────────────────────────────
            ['title' => 'Digital Marketing Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/digital-marketing', 'field' => 'Marketing', 'is_free' => false, 'description' => 'University of Illinois specialization on SEO, social media, digital analytics.', 'rating' => 4.5],
            ['title' => 'Google Digital Marketing Certificate', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/professional-certificates/google-digital-marketing-ecommerce', 'field' => 'Marketing', 'is_free' => false, 'description' => 'Google-designed program covering digital marketing and e-commerce fundamentals.', 'rating' => 4.8],
            ['title' => 'The Complete Digital Marketing Course', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/learn-digital-marketing-course/', 'field' => 'Marketing', 'is_free' => false, 'description' => 'Master SEO, YouTube, Facebook, Google Ads, and social media marketing.', 'rating' => 4.5],

            // ─── Engineering ────────────────────────────────────────────
            ['title' => 'Engineering Project Management', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/engineering-project-management', 'field' => 'Engineering', 'is_free' => false, 'description' => 'Rice University specialization on managing engineering projects and teams.', 'rating' => 4.6],
            ['title' => 'Introduction to Engineering Mechanics', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/engineering-mechanics-statics', 'field' => 'Engineering', 'is_free' => false, 'description' => 'Georgia Tech course covering statics and engineering mechanics fundamentals.', 'rating' => 4.7],
            ['title' => 'AutoCAD Complete Course', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/autocad-2d-and-3d-practice-drawings/', 'field' => 'Engineering', 'is_free' => false, 'description' => 'Master AutoCAD 2D and 3D for engineering design and drafting.', 'rating' => 4.5],

            // ─── Education ──────────────────────────────────────────────
            ['title' => 'Foundations of Teaching for Learning', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/foundations-teaching-learning', 'field' => 'Education', 'is_free' => false, 'description' => 'Commonwealth Education Trust specialization on teaching skills and pedagogy.', 'rating' => 4.6],
            ['title' => 'Instructional Design Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/instructional-design', 'field' => 'Education', 'is_free' => false, 'description' => 'Learn to design effective learning experiences using instructional design models.', 'rating' => 4.5],
            ['title' => 'Teaching English Online', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/teach-english-online', 'field' => 'Education', 'is_free' => false, 'description' => 'Learn strategies and tools to effectively teach English in online settings.'],

            // ─── Healthcare / Nursing ────────────────────────────────────
            ['title' => 'Anatomy Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/anatomy', 'field' => 'Healthcare / Nursing', 'is_free' => false, 'description' => 'University of Michigan specialization covering human body anatomy systems.', 'rating' => 4.8],
            ['title' => 'Patient Safety and Quality Improvement', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/patient-safety', 'field' => 'Healthcare / Nursing', 'is_free' => false, 'description' => 'Johns Hopkins course on improving patient safety and healthcare quality.', 'rating' => 4.7],
            ['title' => 'Epidemiology: The Basic Science of Public Health', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/epidemiology', 'field' => 'Healthcare / Nursing', 'is_free' => false, 'description' => 'UNC Chapel Hill course on epidemiology fundamentals and public health.', 'rating' => 4.8],

            // ─── Psychology ─────────────────────────────────────────────
            ['title' => 'Introduction to Psychology', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/introduction-psychology', 'field' => 'Psychology', 'is_free' => false, 'description' => 'Yale University course on the science of the mind and behavior.', 'rating' => 4.9],
            ['title' => 'Positive Psychology Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/positivepsychology', 'field' => 'Psychology', 'is_free' => false, 'description' => 'University of Pennsylvania specialization on well-being, resilience, and positive psychology.', 'rating' => 4.8],
            ['title' => 'Social Psychology', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/social-psychology', 'field' => 'Psychology', 'is_free' => false, 'description' => 'Wesleyan University course exploring human behavior in social contexts.', 'rating' => 4.7],

            // ─── Hospitality and Tourism ─────────────────────────────────
            ['title' => 'Hospitality Customer Experience', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/hospitality-customer-experience', 'field' => 'Hospitality and Tourism', 'is_free' => false, 'description' => 'Learn customer experience management in the hospitality industry.', 'rating' => 4.5],
            ['title' => 'Hotel Management Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/hotel-management', 'field' => 'Hospitality and Tourism', 'is_free' => false, 'description' => 'ESSEC Business School specialization on hotel management and hospitality strategy.', 'rating' => 4.6],

            // ─── Architecture ───────────────────────────────────────────
            ['title' => 'Making Architecture', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/making-architecture', 'field' => 'Architecture', 'is_free' => false, 'description' => 'IE Business School course exploring architectural design and construction.', 'rating' => 4.4],
            ['title' => 'Architectural Design and Visualization', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/revit-architecture/', 'field' => 'Architecture', 'is_free' => false, 'description' => 'Master Revit for architectural design, BIM modeling, and visualization.', 'rating' => 4.5],

            // ─── Agriculture ────────────────────────────────────────────
            ['title' => 'Sustainable Agricultural Land Management', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/sustainable-agricultural-land-management', 'field' => 'Agriculture', 'is_free' => false, 'description' => 'Learn sustainable land management practices for agricultural systems.', 'rating' => 4.5],
            ['title' => 'Feeding the World', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/feeding-the-world', 'field' => 'Agriculture', 'is_free' => false, 'description' => 'University of Pennsylvania course on sustainable food systems and global food security.', 'rating' => 4.6],

            // ─── Arts and Multimedia ─────────────────────────────────────
            ['title' => 'Graphic Design Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/graphic-design', 'field' => 'Arts and Multimedia', 'is_free' => false, 'description' => 'CalArts specialization on visual communication through graphic design.', 'rating' => 4.7],
            ['title' => 'Photography and Photo Editing Masterclass', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/photography-masterclass-complete-guide-to-photography/', 'field' => 'Arts and Multimedia', 'is_free' => false, 'description' => 'Complete photography course covering shooting, editing, and storytelling.', 'rating' => 4.6],
            ['title' => 'Video Editing with Adobe Premiere Pro', 'provider' => 'Udemy', 'url' => 'https://www.udemy.com/course/adobe-premiere-pro-video-editing/', 'field' => 'Arts and Multimedia', 'is_free' => false, 'description' => 'Master video editing with Adobe Premiere Pro from beginner to advanced.', 'rating' => 4.5],

            // ─── Communications ─────────────────────────────────────────
            ['title' => 'Communication Skills for Engineers', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/communication-skills-engineers', 'field' => 'Communications', 'is_free' => false, 'description' => 'Rice University specialization on writing, speaking, and professional communication.', 'rating' => 4.6],
            ['title' => 'Public Speaking Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/public-speaking', 'field' => 'Communications', 'is_free' => false, 'description' => 'University of Washington specialization on confident public speaking and presentations.', 'rating' => 4.7],

            // ─── Logistics and Supply Chain ──────────────────────────────
            ['title' => 'Supply Chain Management Specialization', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/specializations/supply-chain-management', 'field' => 'Logistics and Supply Chain', 'is_free' => false, 'description' => 'Rutgers specialization on logistics, procurement, operations, and supply chain strategy.', 'rating' => 4.7],
            ['title' => 'Operations Management', 'provider' => 'Coursera', 'url' => 'https://www.coursera.org/learn/wharton-operations', 'field' => 'Logistics and Supply Chain', 'is_free' => false, 'description' => 'Wharton course on operations management, process analysis, and quality improvement.', 'rating' => 4.6],
        ];

        // Clear existing courses and re-seed
        Course::truncate();

        foreach ($courses as $course) {
            Course::create($course);
        }

        $this->command->info('Seeded ' . count($courses) . ' courses successfully!');
    }
}
