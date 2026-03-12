import pandas as pd
import os

data_dir = r"C:\Users\azi\Desktop\UR\AI-Resume-Analyzer\App\data"

new_roles = [
    {"role": "Business Manager", "field": "Business Administration", "description": "Oversees business operations and manages teams to ensure company goals are met.", "required_skills": "Management;Leadership;Business Strategy;Operations;Communication", "experience_level": "Experienced", "avg_salary_range": "₱60,000 - ₱120,000/month"},
    {"role": "Operations Analyst", "field": "Business Administration", "description": "Analyzes business operations and workflows to improve efficiency and reduce costs.", "required_skills": "Data Analysis;Process Improvement;Operations;Excel;Problem Solving", "experience_level": "Intermediate", "avg_salary_range": "₱35,000 - ₱70,000/month"},
    {"role": "Accountant", "field": "Accounting and Finance", "description": "Prepares financial records, ensures taxes are paid, and audits financial statements.", "required_skills": "Accounting;Tax Preparation;Financial Reporting;QuickBooks;Excel", "experience_level": "Intermediate", "avg_salary_range": "₱30,000 - ₱60,000/month"},
    {"role": "Financial Analyst", "field": "Accounting and Finance", "description": "Analyzes financial data to help companies make investment and business decisions.", "required_skills": "Financial Modeling;Valuation;Excel;Data Analysis;Forecasting", "experience_level": "Experienced", "avg_salary_range": "₱40,000 - ₱80,000/month"},
    {"role": "Marketing Specialist", "field": "Marketing", "description": "Develops and executes marketing campaigns to promote products or services.", "required_skills": "Digital Marketing;SEO;Content Creation;Social Media;Analytics", "experience_level": "Intermediate", "avg_salary_range": "₱30,000 - ₱65,000/month"},
    {"role": "Market Research Analyst", "field": "Marketing", "description": "Studies market conditions to examine potential sales of a product or service.", "required_skills": "Market Research;Data Analysis;Statistics;Survey Design;Reporting", "experience_level": "Intermediate", "avg_salary_range": "₱35,000 - ₱70,000/month"},
    {"role": "Civil Engineer", "field": "Engineering", "description": "Designs and supervises large construction projects, including roads, buildings, and bridges.", "required_skills": "AutoCAD;Structural Analysis;Project Management;Construction;Design", "experience_level": "Experienced", "avg_salary_range": "₱40,000 - ₱90,000/month"},
    {"role": "Mechanical Engineer", "field": "Engineering", "description": "Designs, develops, and tests mechanical and thermal sensors and devices.", "required_skills": "SolidWorks;Mechanical Design;Thermodynamics;CAD;Manufacturing", "experience_level": "Experienced", "avg_salary_range": "₱40,000 - ₱85,000/month"},
    {"role": "Teacher", "field": "Education", "description": "Plans lessons and instructs students in specific subjects.", "required_skills": "Teaching;Lesson Planning;Classroom Management;Communication;Mentoring", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱50,000/month"},
    {"role": "Instructional Designer", "field": "Education", "description": "Develops educational courses and training materials for learning programs.", "required_skills": "Curriculum Design;E-Learning;Adult Learning;Storyboarding;LMS", "experience_level": "Intermediate", "avg_salary_range": "₱35,000 - ₱75,000/month"},
    {"role": "Registered Nurse", "field": "Healthcare / Nursing", "description": "Provides patient care, records medical history, and administers medication.", "required_skills": "Patient Care;Clinical Skills;BLS;Vital Signs;Medication Administration", "experience_level": "Experienced", "avg_salary_range": "₱30,000 - ₱60,000/month"},
    {"role": "Medical Technologist", "field": "Healthcare / Nursing", "description": "Performs tests on body fluids, tissues, and cells to help doctors diagnose diseases.", "required_skills": "Laboratory Tech;Analysis;Quality Control;Phlebotomy;Microbiology", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱55,000/month"},
    {"role": "Hotel Manager", "field": "Hospitality and Tourism", "description": "Manages hotel operations including front desk, housekeeping, and guest services.", "required_skills": "Hospitality Management;Customer Service;Operations;Leadership;Revenue Management", "experience_level": "Experienced", "avg_salary_range": "₱50,000 - ₱100,000/month"},
    {"role": "Travel Consultant", "field": "Hospitality and Tourism", "description": "Advises clients on travel arrangements and books flights, hotels, and tours.", "required_skills": "Customer Service;Booking Systems;Sales;Geography;Communication", "experience_level": "Intermediate", "avg_salary_range": "₱20,000 - ₱45,000/month"},
    {"role": "Architect", "field": "Architecture", "description": "Designs buildings and other structures, ensuring they are functional, safe, and aesthetically pleasing.", "required_skills": "Architectural Design;AutoCAD;Revit;3D Modeling;Project Management", "experience_level": "Experienced", "avg_salary_range": "₱40,000 - ₱90,000/month"},
    {"role": "Interior Designer", "field": "Architecture", "description": "Plans and designs interior spaces to make them functional, safe, and beautiful.", "required_skills": "Space Planning;Interior Architecture;AutoCAD;SketchUp;Design", "experience_level": "Intermediate", "avg_salary_range": "₱30,000 - ₱70,000/month"},
    {"role": "HR Specialist", "field": "Psychology", "description": "Handles employee relations, recruitment, and organizational development.", "required_skills": "Recruitment;Employee Relations;Onboarding;HR Policies;Interviewing", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱60,000/month"},
    {"role": "Clinical Psychologist", "field": "Psychology", "description": "Diagnoses and treats mental, emotional, and behavioral disorders.", "required_skills": "Psychological Assessment;Counseling;Therapy;Mental Health;Empathy", "experience_level": "Experienced", "avg_salary_range": "₱40,000 - ₱80,000/month"},
    {"role": "Agriculturist", "field": "Agriculture", "description": "Researches and implements farming techniques to improve crop and livestock production.", "required_skills": "Farming Techniques;Crop Production;Soil Science;Research;Pest Management", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱60,000/month"},
    {"role": "Farm Manager", "field": "Agriculture", "description": "Oversees the daily operations of an agricultural estate or farm.", "required_skills": "Farm Management;Operations;Budgeting;Agriculture;Supervision", "experience_level": "Experienced", "avg_salary_range": "₱30,000 - ₱70,000/month"},
    {"role": "Graphic Designer", "field": "Arts and Multimedia", "description": "Creates visual concepts by hand or using computer software to communicate ideas.", "required_skills": "Adobe Photoshop;Illustrator;Typography;Layout Design;Creativity", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱60,000/month"},
    {"role": "Video Editor", "field": "Arts and Multimedia", "description": "Manipulates and edits film pieces in a way that is invisible to the audience.", "required_skills": "Video Editing;Adobe Premiere;After Effects;Color Grading;Storytelling", "experience_level": "Intermediate", "avg_salary_range": "₱30,000 - ₱70,000/month"},
    {"role": "Public Relations Specialist", "field": "Communications", "description": "Builds and maintains a positive public image for a company or organization.", "required_skills": "Public Relations;Media Relations;Press Releases;Communication;Writing", "experience_level": "Intermediate", "avg_salary_range": "₱30,000 - ₱65,000/month"},
    {"role": "Corporate Communications Manager", "field": "Communications", "description": "Manages internal and external communications for a corporation.", "required_skills": "Corporate Communications;Internal Communications;Strategy;Writing;Leadership", "experience_level": "Experienced", "avg_salary_range": "₱50,000 - ₱100,000/month"},
    {"role": "Supply Chain Analyst", "field": "Logistics and Supply Chain", "description": "Analyzes supply chain processes to identify areas for improvement and cost reduction.", "required_skills": "Supply Chain Management;Data Analysis;Logistics;Inventory Management;Excel", "experience_level": "Intermediate", "avg_salary_range": "₱35,000 - ₱75,000/month"},
    {"role": "Logistics Coordinator", "field": "Logistics and Supply Chain", "description": "Oversees the routing and scheduling of delivery vehicles and drivers.", "required_skills": "Logistics;Supply Chain;Scheduling;Inventory Control;Coordination", "experience_level": "Intermediate", "avg_salary_range": "₱25,000 - ₱55,000/month"}
]

new_skills = [
    {"skill": "Management", "category": "Soft Skill", "field": "Business Administration", "importance": "High"},
    {"skill": "Business Strategy", "category": "Business", "field": "Business Administration", "importance": "High"},
    {"skill": "Operations", "category": "Business", "field": "Business Administration", "importance": "Medium"},
    {"skill": "Accounting", "category": "Finance", "field": "Accounting and Finance", "importance": "High"},
    {"skill": "Financial Reporting", "category": "Finance", "field": "Accounting and Finance", "importance": "High"},
    {"skill": "Financial Modeling", "category": "Finance", "field": "Accounting and Finance", "importance": "High"},
    {"skill": "QuickBooks", "category": "Tool", "field": "Accounting and Finance", "importance": "Medium"},
    {"skill": "Digital Marketing", "category": "Marketing", "field": "Marketing", "importance": "High"},
    {"skill": "SEO", "category": "Marketing", "field": "Marketing", "importance": "High"},
    {"skill": "Content Creation", "category": "Marketing", "field": "Marketing", "importance": "Medium"},
    {"skill": "Market Research", "category": "Marketing", "field": "Marketing", "importance": "High"},
    {"skill": "AutoCAD", "category": "Design Tool", "field": "Engineering", "importance": "High"},
    {"skill": "Structural Analysis", "category": "Engineering", "field": "Engineering", "importance": "High"},
    {"skill": "SolidWorks", "category": "Design Tool", "field": "Engineering", "importance": "High"},
    {"skill": "Project Management", "category": "Management", "field": "Engineering", "importance": "Medium"},
    {"skill": "Teaching", "category": "Education", "field": "Education", "importance": "High"},
    {"skill": "Lesson Planning", "category": "Education", "field": "Education", "importance": "High"},
    {"skill": "Curriculum Design", "category": "Education", "field": "Education", "importance": "High"},
    {"skill": "E-Learning", "category": "Education", "field": "Education", "importance": "Medium"},
    {"skill": "Patient Care", "category": "Healthcare", "field": "Healthcare / Nursing", "importance": "High"},
    {"skill": "Clinical Skills", "category": "Healthcare", "field": "Healthcare / Nursing", "importance": "High"},
    {"skill": "BLS", "category": "Certification", "field": "Healthcare / Nursing", "importance": "High"},
    {"skill": "Laboratory Tech", "category": "Healthcare", "field": "Healthcare / Nursing", "importance": "Medium"},
    {"skill": "Hospitality Management", "category": "Hospitality", "field": "Hospitality and Tourism", "importance": "High"},
    {"skill": "Customer Service", "category": "Soft Skill", "field": "Hospitality and Tourism", "importance": "High"},
    {"skill": "Booking Systems", "category": "Tool", "field": "Hospitality and Tourism", "importance": "Medium"},
    {"skill": "Revenue Management", "category": "Finance", "field": "Hospitality and Tourism", "importance": "Medium"},
    {"skill": "Architectural Design", "category": "Architecture", "field": "Architecture", "importance": "High"},
    {"skill": "Revit", "category": "Design Tool", "field": "Architecture", "importance": "High"},
    {"skill": "3D Modeling", "category": "Design Tool", "field": "Architecture", "importance": "Medium"},
    {"skill": "Space Planning", "category": "Design", "field": "Architecture", "importance": "High"},
    {"skill": "Recruitment", "category": "HR", "field": "Psychology", "importance": "High"},
    {"skill": "Employee Relations", "category": "HR", "field": "Psychology", "importance": "High"},
    {"skill": "Psychological Assessment", "category": "Healthcare", "field": "Psychology", "importance": "High"},
    {"skill": "Counseling", "category": "Healthcare", "field": "Psychology", "importance": "Medium"},
    {"skill": "Farming Techniques", "category": "Agriculture", "field": "Agriculture", "importance": "High"},
    {"skill": "Crop Production", "category": "Agriculture", "field": "Agriculture", "importance": "High"},
    {"skill": "Farm Management", "category": "Agriculture", "field": "Agriculture", "importance": "High"},
    {"skill": "Soil Science", "category": "Agriculture", "field": "Agriculture", "importance": "Medium"},
    {"skill": "Adobe Photoshop", "category": "Design Tool", "field": "Arts and Multimedia", "importance": "High"},
    {"skill": "Illustrator", "category": "Design Tool", "field": "Arts and Multimedia", "importance": "Medium"},
    {"skill": "Video Editing", "category": "Multimedia", "field": "Arts and Multimedia", "importance": "High"},
    {"skill": "Adobe Premiere", "category": "Design Tool", "field": "Arts and Multimedia", "importance": "High"},
    {"skill": "Public Relations", "category": "Communications", "field": "Communications", "importance": "High"},
    {"skill": "Media Relations", "category": "Communications", "field": "Communications", "importance": "High"},
    {"skill": "Corporate Communications", "category": "Communications", "field": "Communications", "importance": "High"},
    {"skill": "Writing", "category": "Soft Skill", "field": "Communications", "importance": "Medium"},
    {"skill": "Supply Chain Management", "category": "Logistics", "field": "Logistics and Supply Chain", "importance": "High"},
    {"skill": "Logistics", "category": "Logistics", "field": "Logistics and Supply Chain", "importance": "High"},
    {"skill": "Inventory Management", "category": "Logistics", "field": "Logistics and Supply Chain", "importance": "High"},
    {"skill": "Scheduling", "category": "Soft Skill", "field": "Logistics and Supply Chain", "importance": "Medium"}
]

new_courses = [
    {"field": "Business Administration", "course_name": "Business Foundations Specialization", "course_url": "https://www.coursera.org/specializations/wharton-business-foundations"},
    {"field": "Accounting and Finance", "course_name": "Finance & Quantitative Modeling", "course_url": "https://www.coursera.org/specializations/finance-quantitative-modeling-analysts"},
    {"field": "Marketing", "course_name": "Digital Marketing Specialization", "course_url": "https://www.coursera.org/specializations/digital-marketing"},
    {"field": "Engineering", "course_name": "Engineering Project Management", "course_url": "https://www.coursera.org/specializations/engineering-project-management"},
    {"field": "Education", "course_name": "Foundations of Teaching for Learning", "course_url": "https://www.coursera.org/specializations/foundations-teaching-learning"},
    {"field": "Healthcare / Nursing", "course_name": "Anatomy Specialization", "course_url": "https://www.coursera.org/specializations/anatomy"},
    {"field": "Hospitality and Tourism", "course_name": "Hospitality Customer Experience", "course_url": "https://www.coursera.org/learn/hospitality-customer-experience"},
    {"field": "Architecture", "course_name": "Making Architecture", "course_url": "https://www.coursera.org/learn/making-architecture"},
    {"field": "Psychology", "course_name": "Introduction to Psychology", "course_url": "https://www.coursera.org/learn/introduction-psychology"},
    {"field": "Agriculture", "course_name": "Sustainable Agricultural Land Management", "course_url": "https://www.coursera.org/learn/sustainable-agricultural-land-management"},
    {"field": "Arts and Multimedia", "course_name": "Graphic Design Specialization", "course_url": "https://www.coursera.org/specializations/graphic-design"},
    {"field": "Communications", "course_name": "Communication Skills for Engineers", "course_url": "https://www.coursera.org/specializations/communication-skills-engineers"},
    {"field": "Logistics and Supply Chain", "course_name": "Supply Chain Management Specialization", "course_url": "https://www.coursera.org/specializations/supply-chain-management"}
]

def append_to_csv(filename, new_data):
    filepath = os.path.join(data_dir, filename)
    df_new = pd.DataFrame(new_data)
    if os.path.exists(filepath):
        df_old = pd.read_csv(filepath)
        df_combined = pd.concat([df_old, df_new], ignore_index=True)
        # Simple deduplication based on core columns
        if "role" in df_combined.columns:
            df_combined = df_combined.drop_duplicates(subset=["role", "field"])
        elif "skill" in df_combined.columns:
            df_combined = df_combined.drop_duplicates(subset=["skill", "field"])
        elif "course_name" in df_combined.columns:
            df_combined = df_combined.drop_duplicates(subset=["course_name", "field"])
        df_combined.to_csv(filepath, index=False)
        print(f"Updated {filename} with new curated records. Total rows: {len(df_combined)}")
    else:
        print(f"File not found: {filepath}")

if __name__ == "__main__":
    append_to_csv('job_roles.csv', new_roles)
    append_to_csv('skills_dataset.csv', new_skills)
    append_to_csv('courses.csv', new_courses)
