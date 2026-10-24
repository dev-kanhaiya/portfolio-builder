<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private array $themes = [
        'light' => 'Light Modern',
        'dark' => 'Dark Cyber',
        'glass' => 'Glassmorphism',
        'terminal' => 'Terminal / CLI',
        'corporate' => 'Corporate Clean',
    ];

    private array $designs = [
        'studio' => 'Studio Portfolio',
        'sidebar' => 'Sidebar Profile',
        'showcase' => 'Project Gallery',
        'editorial' => 'Editorial',
        'minimal' => 'Minimal',
        'terminal' => 'Terminal',
    ];

    public function dashboard(): View
    {
        $portfolios = auth()->user()->portfolios()->latest()->get();

        return view('dashboard', [
            'portfolios' => $portfolios,
            'designs' => $this->designs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150'],
            'theme_slug' => ['required', Rule::in(array_keys($this->themes))],
            'design_slug' => ['required', Rule::in(array_keys($this->designs))],
        ]);

        $portfolio = auth()->user()->portfolios()->create([
            'title' => $data['title'],
            'slug' => $this->makeUniqueSlug($data['slug'] ?? $data['title']),
            'theme_slug' => $data['theme_slug'],
            'design_slug' => $data['design_slug'],
        ]);

        $portfolio->profile()->create([
            'full_name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);

        return redirect()->route('portfolio.edit', $portfolio)->with('success', 'Portfolio created. Add your details below.');
    }

    public function edit(int $id): View
    {
        $portfolio = auth()->user()->portfolios()
            ->with(['profile', 'skills', 'projects', 'experiences', 'education'])
            ->findOrFail($id);

        $skillCategories = [
            'Backend',
            'Frontend',
            'Databases',
            'DevOps & Deployment',
            'Concepts',
            'Tools',
            'Design',
            'Other',
        ];

        foreach ($portfolio->skills->pluck('category')->filter()->unique() as $category) {
            if (! in_array($category, $skillCategories, true)) {
                $skillCategories[] = $category;
            }
        }

        return view('portfolio.editor', [
            'portfolio' => $portfolio,
            'themes' => $this->themes,
            'designs' => $this->designs,
            'skillCategories' => $skillCategories,
        ]);
    }

    public function download(int $id): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $portfolio = auth()->user()->portfolios()
            ->with(['profile', 'projects'])
            ->findOrFail($id);

        if (blank($portfolio->custom_html)) {
            return redirect()->route('portfolio.edit', $portfolio)
                ->with('error', 'Save your portfolio details before downloading it.');
        }

        if (! class_exists(\ZipArchive::class)) {
            return back()->with('error', 'ZIP download needs the PHP ZIP extension enabled on the server.');
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'portfolio-');
        $zip = new \ZipArchive();

        if ($temporaryFile === false || $zip->open($temporaryFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            if ($temporaryFile !== false) {
                @unlink($temporaryFile);
            }

            return back()->with('error', 'The portfolio ZIP could not be created. Please try again.');
        }

        $addImage = function (?string $imagePath, string $fileName) use ($zip): ?string {
            if (! $imagePath || ! Storage::disk('public')->exists($imagePath)) {
                return null;
            }

            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'webp';
            $archivePath = 'assets/'.$fileName.'.'.$extension;

            if (! $zip->addFromString($archivePath, Storage::disk('public')->get($imagePath))) {
                return null;
            }

            return $archivePath;
        };

        $html = $portfolio->custom_html;
        $avatarPath = $addImage($portfolio->profile?->avatar_path, 'profile');
        $coverPath = $addImage($portfolio->profile?->cover_path, 'cover');

        $html = str_replace('%%AVATAR%%', e($avatarPath ?? ''), $html);
        $html = str_replace('%%COVER%%', e($coverPath ?? ''), $html);

        $placeholderPath = 'assets/project-placeholder.svg';
        $placeholderFile = public_path('images/project-placeholder.svg');
        $placeholderAdded = false;

        foreach ($portfolio->projects as $index => $project) {
            $imagePath = $addImage($project->image_path, 'project-'.$index);

            if (! $imagePath && ! $placeholderAdded && is_file($placeholderFile)) {
                $zip->addFile($placeholderFile, $placeholderPath);
                $placeholderAdded = true;
            }

            $html = str_replace('%%PROJECT_'.$index.'%%', e($imagePath ?? $placeholderPath), $html);
        }

        $zip->addFromString('index.html', $html);
        $zip->addFromString('README.txt', "Extract this ZIP, then open index.html in a browser.\n");
        $zip->close();

        return response()->download($temporaryFile, (Str::slug($portfolio->title) ?: 'portfolio').'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $portfolio = auth()->user()->portfolios()
            ->with(['profile', 'projects'])
            ->findOrFail($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('portfolios', 'slug')->ignore($portfolio->id)],
            'theme_slug' => ['required', Rule::in(array_keys($this->themes))],
            'design_slug' => ['required', Rule::in(array_keys($this->designs))],
            'custom_html' => ['nullable', 'string', 'max:500000'],
            'profile.full_name' => ['required', 'string', 'max:120'],
            'profile.headline' => ['nullable', 'string', 'max:180'],
            'profile.bio' => ['nullable', 'string', 'max:3000'],
            'profile.email' => ['nullable', 'email', 'max:255'],
            'profile.phone' => ['nullable', 'string', 'max:40'],
            'profile.location' => ['nullable', 'string', 'max:120'],
            'profile.github_url' => ['nullable', 'url', 'max:255'],
            'profile.linkedin_url' => ['nullable', 'url', 'max:255'],
            'profile.website_url' => ['nullable', 'url', 'max:255'],
            'profile.avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'profile.cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['nullable', 'string', 'max:80'],
            'skills.*.category' => ['nullable', 'string', 'max:80'],
            'skills.*.proficiency' => ['nullable', 'integer', 'min:0', 'max:100'],
            'projects' => ['nullable', 'array'],
            'projects.*.id' => ['nullable', 'integer'],
            'projects.*.title' => ['nullable', 'string', 'max:120'],
            'projects.*.description' => ['nullable', 'string', 'max:2000'],
            'projects.*.tags' => ['nullable', 'string', 'max:255'],
            'projects.*.demo_url' => ['nullable', 'url', 'max:255'],
            'projects.*.github_url' => ['nullable', 'url', 'max:255'],
            'projects.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.company' => ['nullable', 'string', 'max:120'],
            'experiences.*.role' => ['nullable', 'string', 'max:120'],
            'experiences.*.description' => ['nullable', 'string', 'max:2000'],
            'experiences.*.start_date' => ['nullable', 'date'],
            'experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:experiences.*.start_date'],
            'experiences.*.current_job' => ['nullable', 'boolean'],
            'education' => ['nullable', 'array'],
            'education.*.institution' => ['nullable', 'string', 'max:160'],
            'education.*.degree' => ['nullable', 'string', 'max:120'],
            'education.*.field' => ['nullable', 'string', 'max:120'],
            'education.*.description' => ['nullable', 'string', 'max:1500'],
            'education.*.start_date' => ['nullable', 'date'],
            'education.*.end_date' => ['nullable', 'date', 'after_or_equal:education.*.start_date'],
        ]);

        DB::transaction(function () use ($request, $data, $portfolio): void {
            $portfolio->update([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'theme_slug' => $data['theme_slug'],
                'design_slug' => $data['design_slug'],
                'custom_html' => $data['custom_html'] ?? null,
            ]);

            $profile = $portfolio->profile ?? new Profile();
            $profileData = $data['profile'];
            unset($profileData['avatar'], $profileData['cover']);
            $profile->fill($profileData);

            if ($request->hasFile('profile.avatar')) {
                $profile->avatar_path = $this->saveImage($request->file('profile.avatar'), $profile->avatar_path);
            }

            if ($request->hasFile('profile.cover')) {
                $profile->cover_path = $this->saveImage($request->file('profile.cover'), $profile->cover_path);
            }

            $portfolio->profile()->save($profile);

            $portfolio->skills()->delete();
            foreach ($data['skills'] ?? [] as $skill) {
                if (trim($skill['name'] ?? '') === '') {
                    continue;
                }

                $portfolio->skills()->create([
                    'name' => $skill['name'],
                    'category' => $skill['category'] ?? null,
                    'proficiency' => $skill['proficiency'] ?? 50,
                ]);
            }

            $savedProjectIds = [];
            foreach ($data['projects'] ?? [] as $index => $projectData) {
                if (trim($projectData['title'] ?? '') === '') {
                    continue;
                }

                $project = null;
                if (! empty($projectData['id'])) {
                    $project = $portfolio->projects->firstWhere('id', $projectData['id']);
                }

                $project ??= $portfolio->projects()->make();
                $imagePath = $project->image_path;
                if ($request->hasFile("projects.$index.image")) {
                    $imagePath = $this->saveImage($request->file("projects.$index.image"), $imagePath);
                }

                $project->fill([
                    'title' => $projectData['title'],
                    'description' => $projectData['description'] ?? null,
                    'tags' => $projectData['tags'] ?? null,
                    'demo_url' => $projectData['demo_url'] ?? null,
                    'github_url' => $projectData['github_url'] ?? null,
                    'image_path' => $imagePath,
                    'sort_order' => (int) $index,
                ]);
                $portfolio->projects()->save($project);
                $savedProjectIds[] = $project->id;
            }

            $removedProjects = $portfolio->projects()->whereNotIn('id', $savedProjectIds ?: [0])->get();
            foreach ($removedProjects as $project) {
                if ($project->image_path) {
                    Storage::disk('public')->delete($project->image_path);
                }
                $project->delete();
            }

            $portfolio->experiences()->delete();
            foreach ($data['experiences'] ?? [] as $experience) {
                if (trim($experience['company'] ?? '') === '' && trim($experience['role'] ?? '') === '') {
                    continue;
                }

                $portfolio->experiences()->create([
                    'company' => $experience['company'] ?? '',
                    'role' => $experience['role'] ?? '',
                    'description' => $experience['description'] ?? null,
                    'start_date' => $experience['start_date'] ?? null,
                    'end_date' => ! empty($experience['current_job']) ? null : ($experience['end_date'] ?? null),
                    'current_job' => ! empty($experience['current_job']),
                ]);
            }

            $portfolio->education()->delete();
            foreach ($data['education'] ?? [] as $education) {
                if (trim($education['institution'] ?? '') === '' && trim($education['degree'] ?? '') === '') {
                    continue;
                }

                $portfolio->education()->create([
                    'institution' => $education['institution'] ?? '',
                    'degree' => $education['degree'] ?? '',
                    'field' => $education['field'] ?? null,
                    'description' => $education['description'] ?? null,
                    'start_date' => $education['start_date'] ?? null,
                    'end_date' => $education['end_date'] ?? null,
                ]);
            }
        });

        return redirect()->route('portfolio.edit', $portfolio)->with('success', 'Your portfolio has been saved.');
    }

    public function clone(int $id): RedirectResponse
    {
        $source = auth()->user()->portfolios()
            ->with(['profile', 'skills', 'projects', 'experiences', 'education'])
            ->findOrFail($id);

        $copy = auth()->user()->portfolios()->create([
            'title' => $source->title.' Copy',
            'slug' => $this->makeUniqueSlug($source->title.'-copy'),
            'theme_slug' => $source->theme_slug,
            'design_slug' => $source->design_slug,
        ]);

        if ($source->profile) {
            $copy->profile()->create($source->profile->only([
                'full_name', 'headline', 'bio', 'email', 'phone', 'location', 'github_url', 'linkedin_url',
                'website_url',
            ]) + [
                'avatar_path' => $this->copyImage($source->profile->avatar_path),
                'cover_path' => $this->copyImage($source->profile->cover_path),
            ]);
        }

        foreach ($source->skills as $skill) {
            $copy->skills()->create($skill->only(['name', 'category', 'proficiency']));
        }
        foreach ($source->projects as $project) {
            $copy->projects()->create($project->only([
                'title', 'description', 'tags', 'demo_url', 'github_url', 'sort_order',
            ]) + ['image_path' => $this->copyImage($project->image_path)]);
        }
        foreach ($source->experiences as $experience) {
            $copy->experiences()->create($experience->only([
                'company', 'role', 'description', 'start_date', 'end_date', 'current_job',
            ]));
        }
        foreach ($source->education as $education) {
            $copy->education()->create($education->only([
                'institution', 'degree', 'field', 'description', 'start_date', 'end_date',
            ]));
        }

        return redirect()->route('portfolio.edit', $copy)->with('success', 'Portfolio cloned. You can now customize the copy.');
    }

    public function togglePublish(int $id): RedirectResponse
    {
        $portfolio = auth()->user()->portfolios()->findOrFail($id);

        if (! $portfolio->is_published && blank($portfolio->custom_html)) {
            return back()->with('error', 'Save your portfolio details in the editor before publishing.');
        }

        $portfolio->update(['is_published' => ! $portfolio->is_published]);

        $message = $portfolio->is_published ? 'Portfolio published.' : 'Portfolio unpublished.';

        return back()->with('success', $message);
    }

    public function destroy(int $id): RedirectResponse
    {
        $portfolio = auth()->user()->portfolios()->with(['profile', 'projects'])->findOrFail($id);
        $imagePaths = $portfolio->projects->pluck('image_path')->all();

        if ($portfolio->profile) {
            $imagePaths[] = $portfolio->profile->avatar_path;
            $imagePaths[] = $portfolio->profile->cover_path;
        }

        Storage::disk('public')->delete(array_filter($imagePaths));
        $portfolio->delete();

        return redirect()->route('dashboard')->with('success', 'Portfolio deleted.');
    }

    public function publicPortfolio(string $slug): View
    {
        $portfolio = Portfolio::where('slug', $slug)
            ->where('is_published', true)
            ->with(['user', 'profile', 'skills', 'projects', 'experiences', 'education'])
            ->firstOrFail();

        $portfolioHtml = $this->portfolioHtml($portfolio);

        return view('portfolio.public', compact('portfolio', 'portfolioHtml'));
    }

    public function resume(string $slug): View
    {
        $portfolio = Portfolio::where('slug', $slug)
            ->where('is_published', true)
            ->with(['user', 'profile', 'skills', 'projects', 'experiences', 'education'])
            ->firstOrFail();

        return view('portfolio.resume', compact('portfolio'));
    }

    public function resumePreview(int $id): View
    {
        $portfolio = auth()->user()->portfolios()
            ->with(['user', 'profile', 'skills', 'projects', 'experiences', 'education'])
            ->findOrFail($id);

        return view('portfolio.resume', [
            'portfolio' => $portfolio,
            'privatePreview' => true,
        ]);
    }

    private function makeUniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'portfolio';
        $slug = $base;
        $number = 2;

        while (Portfolio::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$number;
            $number++;
        }

        return $slug;
    }

    private function saveImage($file, ?string $oldPath = null): string
    {
        $path = null;

        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));

            if ($image !== false) {
                ob_start();
                $converted = imagewebp($image, null, 82);
                $contents = ob_get_clean();
                imagedestroy($image);

                if ($converted) {
                    $path = 'portfolios/'.Str::uuid().'.webp';
                    Storage::disk('public')->put($path, $contents);
                }
            }
        }

        $path ??= $file->store('portfolios', 'public');

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    private function copyImage(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'webp';
        $copyPath = 'portfolios/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->copy($path, $copyPath);

        return $copyPath;
    }

    private function portfolioHtml(Portfolio $portfolio): string
    {
        $html = $portfolio->custom_html ?? '';
        $avatarUrl = $portfolio->profile?->avatar_path
            ? asset('storage/'.$portfolio->profile->avatar_path)
            : '';
        $coverUrl = $portfolio->profile?->cover_path
            ? asset('storage/'.$portfolio->profile->cover_path)
            : '';

        $html = str_replace('%%AVATAR%%', e($avatarUrl), $html);
        $html = str_replace('%%COVER%%', e($coverUrl), $html);

        foreach ($portfolio->projects as $index => $project) {
            $imageUrl = $project->image_path && Storage::disk('public')->exists($project->image_path)
                ? asset('storage/'.$project->image_path)
                : asset('images/project-placeholder.svg');
            $html = str_replace('%%PROJECT_'.$index.'%%', e($imageUrl), $html);
        }

        return $html;
    }
}
