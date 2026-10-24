<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ImportResumePortfolio extends Command
{
    private const NAME = 'Krishna Rathaur';
    private const EMAIL = 'krishnarathaur0001@gmail.com';
    private const PHONE = '+91 8368003925';
    private const LOCATION = 'Ghaziabad, India';

    protected $signature = 'portfolio:import-resume {--replace : Confirm that existing portfolios can be replaced}';

    protected $description = 'Replace existing portfolios with Krishna Rathaur resume details';

    public function handle(): int
    {
        if (! $this->option('replace')) {
            $this->error('Run this command with --replace to continue.');

            return self::FAILURE;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->error('This command is set up for MySQL. Check DB_CONNECTION in the server .env file.');

            return self::FAILURE;
        }

        $requiredTables = ['users', 'portfolios', 'profiles', 'skills', 'projects', 'experiences', 'education'];
        $missingTables = array_filter($requiredTables, fn (string $table) => ! Schema::hasTable($table));

        if ($missingTables) {
            $this->error('Database tables are missing. Run php artisan migrate --force first.');

            return self::FAILURE;
        }

        if (! $this->confirm('This deletes every existing portfolio and its details. User accounts will stay. Continue?')) {
            $this->info('Nothing was changed.');

            return self::SUCCESS;
        }

        $user = User::query()->where('email', self::EMAIL)->first();
        $password = null;

        if (! $user) {
            $this->warn('No account was found for '.self::EMAIL.'. Create a password for the new account.');
            $password = $this->secret('New account password (at least 8 characters)');

            if (! $password || strlen($password) < 8) {
                $this->error('The password must contain at least 8 characters. Nothing was changed.');

                return self::FAILURE;
            }

            if ($password !== $this->secret('Confirm the new password')) {
                $this->error('The passwords did not match. Nothing was changed.');

                return self::FAILURE;
            }
        }

        $portfolio = DB::transaction(function () use ($user, $password) {
            if ($user) {
                $user->update([
                    'name' => self::NAME,
                    'email' => self::EMAIL,
                    'phone' => self::PHONE,
                    'location' => self::LOCATION,
                ]);
            } else {
                $user = User::create([
                    'name' => self::NAME,
                    'email' => self::EMAIL,
                    'phone' => self::PHONE,
                    'location' => self::LOCATION,
                    'password' => Hash::make($password),
                ]);
            }

            if (Portfolio::query()->exists()) {
                Portfolio::query()->delete();
            }

            $portfolio = $user->portfolios()->create([
                'title' => 'Krishna Rathaur - Full Stack Developer',
                'slug' => 'krishna-rathaur',
                'theme_slug' => 'light',
                'design_slug' => 'editorial',
                'is_published' => true,
            ]);

            $portfolio->profile()->create([
                'full_name' => self::NAME,
                'headline' => 'Full Stack Developer | Laravel, React.js, Node.js',
                'bio' => 'Krishna Rathaur, known as Kanhaiya, is a Full Stack Developer with 1+ year of experience building and deploying production web applications. He has delivered secure REST APIs, role-based access, admin panels, and payment-integrated platforms for e-learning, fintech, and e-commerce clients, contributed improvements to around 20 live websites, and teaches web development to 50+ BCA students.',
                'email' => self::EMAIL,
                'phone' => self::PHONE,
                'location' => self::LOCATION,
                'linkedin_url' => 'https://linkedin.com/in/krishna-rathaur',
            ]);

            $skills = [
                ['PHP', 'Backend'],
                ['Laravel', 'Backend'],
                ['Node.js', 'Backend'],
                ['Express.js', 'Backend'],
                ['REST APIs', 'Backend'],
                ['Redis', 'Backend'],
                ['JavaScript', 'Frontend'],
                ['React.js', 'Frontend'],
                ['HTML', 'Frontend'],
                ['CSS', 'Frontend'],
                ['MySQL', 'Databases'],
                ['PostgreSQL', 'Databases'],
                ['Query Optimization', 'Databases'],
                ['VPS Setup', 'DevOps & Deployment'],
                ['Nginx', 'DevOps & Deployment'],
                ['CI/CD Pipelines', 'DevOps & Deployment'],
                ['SSH', 'DevOps & Deployment'],
                ['Linux Server Hosting', 'DevOps & Deployment'],
                ['Role-Based Access Control', 'Concepts'],
                ['Authentication & Authorization', 'Concepts'],
                ['API Design', 'Concepts'],
                ['Git', 'Tools'],
                ['GitHub', 'Tools'],
                ['Postman', 'Tools'],
                ['VS Code', 'Tools'],
            ];

            foreach ($skills as [$name, $category]) {
                $portfolio->skills()->create([
                    'name' => $name,
                    'category' => $category,
                ]);
            }

            $experiences = [
                [
                    'company' => 'HRIT University, Ghaziabad',
                    'role' => 'Visiting Faculty, Web Development',
                    'description' => 'Teach HTML, CSS, JavaScript, and PHP/Laravel to 50+ first-year BCA students. Design course content and hands-on projects, and mentor students on debugging and project workflow.',
                    'start_date' => '2026-07-01',
                    'end_date' => null,
                    'current_job' => true,
                ],
                [
                    'company' => 'The Night Marketer',
                    'role' => 'Laravel Developer',
                    'description' => 'Built and maintained backend modules with reusable CRUD components. Fixed issues in live modules and worked with a team using Git.',
                    'start_date' => '2026-06-01',
                    'end_date' => '2026-07-31',
                    'current_job' => false,
                ],
                [
                    'company' => 'IQFin',
                    'role' => 'Laravel Developer (Project-Based)',
                    'description' => 'Built secure REST APIs and role-based access for enquiry, inventory, and policy management systems. Designed reusable loan policy workflows and delivered features from requirements through production deployment.',
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-05-31',
                    'current_job' => false,
                ],
                [
                    'company' => 'Master-Tec Universe',
                    'role' => 'Full Stack Developer',
                    'description' => 'Developed full-stack applications from database design to interface delivery. Implemented Redis caching and query optimization that brought API response time to around 50 ms, integrated REST APIs, and resolved production issues.',
                    'start_date' => '2025-07-01',
                    'end_date' => '2026-02-28',
                    'current_job' => false,
                ],
                [
                    'company' => 'Master-Tec Universe',
                    'role' => 'Software Development Intern',
                    'description' => 'Built web pages and application features using React.js, Node.js, and Express.js on live client work while learning production workflows.',
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-06-30',
                    'current_job' => false,
                ],
            ];

            foreach ($experiences as $experience) {
                $portfolio->experiences()->create($experience);
            }

            $projects = [
                [
                    'title' => 'EduLife',
                    'description' => 'E-learning platform with student and admin dashboards, course management, MCQ tests, session booking, payments, invoices, email OTP login, and role-based access for students, teachers, and super admins. Set up the VPS and launched the Laravel application.',
                    'tags' => 'E-learning, Payments, Course Management, Role-Based Access',
                    'demo_url' => 'https://edulife.sg',
                    'github_url' => null,
                    'sort_order' => 0,
                ],
                [
                    'title' => 'Raftaar',
                    'description' => 'Mobile application with a React admin dashboard connected to a Laravel REST API and served through an Nginx reverse proxy.',
                    'tags' => 'React, Laravel REST API, Nginx',
                    'demo_url' => null,
                    'github_url' => null,
                    'sort_order' => 1,
                ],
                [
                    'title' => 'DigiBuggy',
                    'description' => 'E-commerce platform with a custom PC builder, product, quotation, and order modules, plus a full administration panel.',
                    'tags' => 'E-commerce, Custom PC Builder, Administration',
                    'demo_url' => 'https://digibuggy.com',
                    'github_url' => null,
                    'sort_order' => 2,
                ],
                [
                    'title' => 'IQFin Policy Management System',
                    'description' => 'Loan policy platform with an eligibility engine, reports, filters, search, and role-based dashboards.',
                    'tags' => 'Fintech, Loan Eligibility, Reports',
                    'demo_url' => 'https://iqfin.in',
                    'github_url' => null,
                    'sort_order' => 3,
                ],
                [
                    'title' => 'Inventory Management System',
                    'description' => 'Inventory platform with separate Admin and Dealer modules, inventory management, authentication, and communication features.',
                    'tags' => 'Inventory, Admin, Dealer Portal',
                    'demo_url' => 'https://iqfin.in/inventory',
                    'github_url' => null,
                    'sort_order' => 4,
                ],
            ];

            foreach ($projects as $project) {
                $portfolio->projects()->create($project);
            }

            $portfolio->education()->create([
                'institution' => 'CCS University, Meerut',
                'degree' => 'Bachelor of Computer Applications (BCA)',
                'field' => 'Computer Applications',
            ]);

            return $portfolio;
        });

        $baseUrl = rtrim(config('app.url'), '/');

        $this->info('The resume portfolio and resume page are ready.');
        $this->line('Portfolio: '.$baseUrl.'/p/'.$portfolio->slug);
        $this->line('Resume: '.$baseUrl.'/p/'.$portfolio->slug.'/resume');

        return self::SUCCESS;
    }
}
