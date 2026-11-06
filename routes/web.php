<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Berita
Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{post}', [NewsController::class, 'show'])->name('news.show');
Route::get('/kategori/{category}', [NewsController::class, 'category'])->name('news.category');

Route::middleware('auth')->group(function () {
    Route::post('/berita/{post}/komentar', [NewsController::class, 'comment'])->middleware('throttle:10,1')->name('news.comment');
    Route::delete('/komentar/{comment}', [NewsController::class, 'destroyComment'])->name('comments.destroy');
    Route::post('/berita/{post}/bookmark', [NewsController::class, 'bookmark'])->name('news.bookmark');
    Route::post('/berita/{post}/laporkan', [NewsController::class, 'report'])->middleware('throttle:5,1')->name('news.report');
});

// Halaman statis
Route::get('/tentang-kami', [PageController::class, 'about'])->name('about');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Profil user
Route::prefix('profil')->name('profile.')->middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/', 'marked')->name('marked');
    Route::get('/kirim-berita', 'create')->name('create');
    Route::post('/kirim-berita', 'store')->name('store');
    Route::get('/berita-saya', 'posts')->name('posts');
    Route::get('/berita-saya/{post}/edit', 'editPost')->name('posts.edit');
    Route::put('/berita-saya/{post}', 'updatePost')->name('posts.update');
    Route::delete('/berita-saya/{post}', 'destroyPost')->name('posts.destroy');
    Route::get('/edit', 'edit')->name('edit');
    Route::put('/edit', 'update')->name('update');
});

// Dashboard admin (AdminLTE)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::delete('posts/bulk', [Admin\PostController::class, 'bulkDestroy'])->name('posts.bulk-destroy');
    Route::patch('posts/{post}/featured', [Admin\PostController::class, 'toggleFeatured'])->name('posts.featured');
    Route::resource('posts', Admin\PostController::class)->except('show');

    Route::resource('categories', Admin\CategoryController::class)->except('show');

    Route::delete('tags/unused', [Admin\TagController::class, 'prune'])->name('tags.prune');
    Route::resource('tags', Admin\TagController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::delete('comments/bulk', [Admin\CommentController::class, 'bulkDestroy'])->name('comments.bulk-destroy');
    Route::resource('comments', Admin\CommentController::class)->only(['index', 'edit', 'update', 'destroy']);

    Route::resource('users', Admin\UserController::class)->except('show');

    Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('reports/{report}/toggle', [Admin\ReportController::class, 'toggle'])->name('reports.toggle');
    Route::delete('reports/{report}', [Admin\ReportController::class, 'destroy'])->name('reports.destroy');
    Route::delete('reports/{report}/post', [Admin\ReportController::class, 'destroyPost'])->name('reports.destroy-post');
});
