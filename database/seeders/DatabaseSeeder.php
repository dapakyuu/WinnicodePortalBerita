<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostRating;
use App\Models\PostView;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Isi awal database: kategori, akun (admin + penulis), dan artikel pembuka
 * sesuai konten desain Figma. Konten selanjutnya dibuat lewat fitur "Send Post".
 *
 * Akun login lokal:  daffa / password  (admin)   —   louis / password  (user)
 * Di production password admin diambil dari ADMIN_PASSWORD (wajib), dan akun
 * penulis contoh diberi password acak agar tidak bisa dipakai login orang lain.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        mt_srand(2025);

        /* ---------- Kategori ---------- */
        $categories = collect([
            ['Food', 'images/tags/food.png'],
            ['Animal', 'images/tags/animal.png'],
            ['Car', 'images/tags/car.png'],
            ['Sport', 'images/tags/sport.png'],
            ['Music', 'images/tags/music.png'],
            ['Technology', 'images/tags/technology.png'],
            ['Abstract', 'images/tags/abstract.png'],
            ['Nature', 'images/tags/nature.png'],
            ['Culture', 'images/instagram/dance-01.png'],
            ['Fashion', 'images/instagram/dance-03.png'],
            ['Healthy Living', 'images/posts/food-07.png'],
            ['Esports', 'images/slider/slide-1.png'],
        ])->mapWithKeys(fn ($c) => [$c[0] => Category::create([
            'name' => $c[0], 'slug' => Str::slug($c[0]), 'image' => $c[1],
        ])]);

        /* ---------- User ---------- */
        $production = app()->isProduction();
        $adminPassword = config('app.admin_password') ?: ($production
            ? throw new \RuntimeException('ADMIN_PASSWORD wajib diisi untuk seeding di production.')
            : 'password');
        $make = fn (string $name, string $username, ?string $avatar, string $role = 'user') => User::create([
            'name' => $name,
            'username' => $username,
            'email' => "{$username}@winnicode.com",
            'password' => $role === 'admin' ? $adminPassword : ($production ? Str::random(40) : 'password'),
            'avatar' => $avatar,
            'role' => $role,
            'bio' => "Penulis di Portal Berita WinniCode.",
        ]);

        $users = collect([
            'daffa' => $make('Daffa Al-Fathir Ismail', 'daffa', 'images/avatar/daffa.png', 'admin'),
            'louis' => $make('Louis Hoebregts', 'louis', 'images/avatar/avatar-01.png'),
            'james' => $make('James', 'james', 'images/avatar/avatar-12.png'),
            'mary' => $make('Mary', 'mary', 'images/avatar/avatar-02.png'),
            'jon' => $make('Jon Kantner', 'jon', 'images/avatar/avatar-10.png'),
            'robert' => $make('Robert', 'robert', 'images/avatar/avatar-05.png'),
            'cassie' => $make('Cassie Evans', 'cassie', 'images/avatar/avatar-05.png'),
            'linda' => $make('Linda', 'linda', 'images/avatar/avatar-03.png'),
            'patricia' => $make('Patricia', 'patricia', 'images/avatar/avatar-04.png'),
            'ellsmartx' => $make('Ellsmartx', 'ellsmartx', null),
            'cassia' => $make('Cassia', 'cassia', null),
            'amanda' => $make('Amanda', 'amanda', null),
            'denis' => $make('Denis Simonassi', 'denis', null),
        ]);
        $users['daffa']->update(['banner' => 'images/tags/abstract.png']);

        /* ---------- Berita ---------- */
        $closing = "Kami akan terus memperbarui informasi seputar topik ini. Bagikan pendapatmu di kolom komentar dan jangan lupa simpan artikel ini agar mudah dibaca kembali.";

        $articles = [
            ['T1 Valorant Juara VCT Masters Bangkok!', 'Esports', 'james', 'images/slider/slide-1.png', '2025-03-02', true, 'valorant, esports, t1',
                "Penantian lama Stax dan Buzz untuk menjadi juara di kancah internasional akhirnya selesai setelah T1 berhasil mengalahkan G2 di Grand Finals. Meteor dinobatkan sebagai MVP dengan penampilan yang gemilang.\n\n## Perjalanan T1 menuju gelar juara\n\nT1 tampil konsisten sejak fase grup dan hanya kehilangan satu map sepanjang babak playoff. Koordinasi tim yang rapi membuat mereka mampu membalikkan keadaan di map penentuan.\n\n" . $closing],
            ['How to Spend the Perfect Day on Croatia’s Most Magical Island', 'Sport', 'louis', 'images/posts/sport-05.png', '2025-07-14', true, 'boating, kayaking, croatia, island, travel, summer, lifestyle',
                "## Don’t wait. The purpose of our lives is to be happy!\n\nUpon arrival, your senses will be rewarded with the pleasant scent of lemongrass oil used to clean the natural wood found throughout the room, creating a relaxing atmosphere within the space. A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart. I am alone, and feel the charm of existence in this spot, which was created for the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the exquisite.\n\n![Surfboards](images/posts/sport-01.png)\n\n## Not how long, but how well you have lived is the main thing.\n\nWhen you are ready to indulge your sense of excitement, check out the range of water- sports opportunities at the resort’s on-site water-sports center. Want to leave your stress on the water? The resort has kayaks, paddleboards, or the low-key pedal boats. Snorkeling equipment is available as well, so you can experience the ever-changing undersea environment. Not only do visitors to a bed and breakfast get a unique perspective on the place they are visiting, they have options for special packages not available in other hotel settings. Bed and breakfasts can partner easily with local businesses for a smoothly organized and highly personalized vacation experience. The Fife and Drum Inn offers options such as the Historic Triangle Package that includes three nights at the Inn, breakfasts, and admissions to historic Williamsburg, Jamestown, and Yorktown. Bed and breakfasts also lend themselves to romance. Part of the charm of a bed and breakfast is the uniqueness; art, décor, and food are integrated to create a complete experience. For example, the Fife and Drum retains the colonial feel of the area in all its guest rooms. Special features include antique furnishings, elegant four poster beds in some guest rooms, as well folk art and artifacts from the restoration period of the historic area available for guests to enjoy."],
            ['Opening Day of Boating Season, Seattle WA', 'Sport', 'james', 'images/posts/sport-05.png', '2025-08-18', false, 'boating, seattle',
                "Of course the Puget Sound is very watery, and where there is water, there are boats. Today is the Grand Opening of Boating Season when traffic gets stalled in the University District (UW) while the Montlake Bridge opens for the parade of boats.\n\n" . $closing],
            ['How to choose the right laptop for programming', 'Technology', 'daffa', 'images/posts/technology-07.png', '2025-07-25', true, 'laptop, programming, gadget',
                "Choosing the right laptop for programming can be a tough process. It’s easy to get confused while researching the various options. There are many different laptop models out there, each with a different set of trade-offs.\n\n## Prioritaskan RAM dan penyimpanan\n\nUntuk pengembangan web dan mobile, RAM minimal 16 GB dan SSD akan membuat proses build jauh lebih cepat.\n\n" . $closing],
            ['How we built the first real self-driving car', 'Car', 'daffa', 'images/posts/car-02.png', '2025-07-14', false, 'self-driving, car, ai',
                "Electric self-driving cars will save millions of lives and significantly accelerate the world’s transition to sustainable energy, but only when they are ready for the real world.\n\n" . $closing],
            ['How to Persuade Your Parents to Buy Fast Food', 'Food', 'jon', 'images/posts/food-07.png', '2025-05-10', false, 'food, fast food',
                "Parents often don’t want to buy fast food. They may be worried that it’s too expensive, unhealthy, or not worth the effort and time.\n\n" . $closing],
            ['12 Mobile UX Design Trends For 2018', 'Technology', 'jon', 'images/posts/technology-04.png', '2025-07-14', false, 'ux, design, mobile',
                "Things move quickly in the mobile app universe. To succeed in the field of mobile UX design, designers must have the foresight and prepare for new challenges around the corner.\n\n" . $closing],
            ['No boat bottomfish: Jetty fishing on the Washington Coast', 'Sport', 'louis', 'images/posts/sport-01.png', '2025-07-13', false, 'fishing, travel',
                "Cast from the rocks to bring home fresh fish tacos in this iconic spring and summer fishery. Are you looking to go fishing and bring home delicious fish without a boat?\n\n" . $closing],
            ['What a Disabled Squirrel Taught Me About Life, Work, and Love', 'Animal', 'james', 'images/posts/animal-03.png', '2025-07-12', false, 'animal, life',
                "Why it helps to know what it’s like, and the exquisite beauty of empathy. These days, the wood patio area just outside my kitchen door is a riot of activity.\n\n" . $closing],
            ['Becoming a Self-Driving Car & Machine Learning Engineer', 'Car', 'cassie', 'images/posts/car-01.png', '2025-07-12', false, 'career, machine learning, car',
                "How I left my full-time job, studied at Udacity, and landed a job at BMW. The past year has been quite a journey for me. A year ago I left my full-time job to learn something new.\n\n" . $closing],
            ['How to Become a Master Designer', 'Abstract', 'linda', 'images/posts/abstract-06.png', '2025-07-10', false, 'design, career',
                "Many outsiders believe that designers are unicorns, gifted or special in some way. We have an innate ability to create gorgeous interfaces, a natural talent for matching complementary colors, an unexplainable intuition.\n\n" . $closing],
            ['This free course can teach you music programming basics in less than an hour', 'Music', 'patricia', 'images/posts/music-04.png', '2025-07-09', false, 'music, programming, course',
                "A Berlin-based music software company just released a free interactive music course that runs right in your browser. One thing that makes it special is how quickly you can make your first beat.\n\n" . $closing],
            ['How to Build A Self-Driving Car In One Month', 'Car', 'mary', 'images/tags/car.png', '2025-06-14', false, 'car, self-driving',
                "Can I learn the necessary computer science to build the software part of a self-driving car in one month? Let’s find out.\n\n" . $closing],
            ['Self-Improvement Has Become An Extreme Sport', 'Sport', 'daffa', 'images/instagram/sport-02.png', '2025-06-10', false, 'self improvement, sport',
                "What we’re told we must do each day to develop and be successful has gone out of control. We need a better way to grow without burning out.\n\n" . $closing],
            ['Why Buying A New Car Makes More Sense Than Used', 'Car', 'mary', 'images/posts/car-01.png', '2025-06-08', false, 'car, finance',
                "Many experts will tell you buying cars used is best for your long-term financial health. Here’s why that might not always be true.\n\n" . $closing],
            ['10 Cooking Lessons To Use In Everyday Life', 'Food', 'jon', 'images/tags/food.png', '2025-06-05', false, 'cooking, food',
                "I recently stumbled upon this quote by Paul Theroux: “Cooking requires confident guesswork and improvisation.” Here are ten lessons from the kitchen that apply everywhere.\n\n" . $closing],
            ['Typography Can Make Or Break Your Design', 'Abstract', 'robert', 'images/posts/music-04.png', '2025-06-01', false, 'typography, design',
                "One of the most important skills you can learn as a designer is how to choose type. This is how to start building that intuition.\n\n" . $closing],
            ['House Boating On Lake Shasta', 'Nature', 'james', 'images/posts/sport-01.png', '2025-05-28', false, 'travel, lake, nature',
                "The best way to spend a long 4th of July weekend. Wake boarding, swimming, barbecues and sunsets on the water.\n\n" . $closing],
            ['Lasagna Is But A Pasta Cake', 'Food', 'jon', 'images/instagram/dance-03.png', '2025-05-20', false, 'food, pasta',
                "Re-envision the description of a common food from a different perspective — it is a pasta cake, and here’s why that matters.\n\n" . $closing],
            ['The Best Headphones For Working From Home', 'Technology', 'daffa', 'images/instagram/sport-02.png', '2025-05-15', false, 'gadget, music, work',
                "Good headphones make long video calls and focused work sessions far more comfortable. We compared comfort, microphone quality, and battery life.\n\n" . $closing],
            ['Why Dogs Make The Best Remote Work Companions', 'Animal', 'linda', 'images/instagram/animal-07.png', '2025-05-12', false, 'animal, dog, work',
                "Studies show that having a pet around while working from home can reduce stress and encourage healthier breaks during the day.\n\n" . $closing],
            ['Traditional Dance Is Finding A New Generation Online', 'Culture', 'patricia', 'images/instagram/dance-01.png', '2025-05-02', false, 'culture, dance',
                "Short videos are helping traditional dances reach young audiences, with dancers sharing tutorials and the stories behind every movement.\n\n" . $closing],
        ];

        $posts = collect();
        foreach ($articles as [$title, $category, $author, $image, $date, $featured, $tags, $content]) {
            $post = Post::create([
                'user_id' => $users[$author]->id,
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'slug' => Post::uniqueSlug($title),
                'content' => $content,
                'image' => $image,
                'is_featured' => $featured,
                'published_at' => Carbon::parse($date . ' ' . sprintf('%02d:%02d', mt_rand(6, 21), mt_rand(0, 59))),
            ]);
            $post->tags()->sync(Tag::idsFromString($tags));
            $posts->push($post);
        }

        /* ---------- Statistik views & rating (8 bulan terakhir) ---------- */
        $readers = $users->values();
        foreach ($posts as $post) {
            $rows = [];
            $count = mt_rand(40, 160);
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'post_id' => $post->id,
                    'user_id' => mt_rand(0, 3) === 0 ? $readers->random()->id : null,
                    'created_at' => now()->subDays(mt_rand(0, 240))->subMinutes(mt_rand(0, 1440)),
                ];
            }
            PostView::insert($rows);
            $post->update(['views' => $count * 25 + mt_rand(0, 900)]);

            foreach ($readers->random(mt_rand(3, 8)) as $reader) {
                PostRating::create([
                    'post_id' => $post->id,
                    'user_id' => $reader->id,
                    'rating' => mt_rand(2, 5),
                    'created_at' => now()->subDays(mt_rand(0, 270)),
                ]);
            }
        }

        /* ---------- Komentar ---------- */
        $croatia = $posts[1];
        $c1 = Comment::create(['post_id' => $croatia->id, 'user_id' => $users['jon']->id, 'body' => 'What a beautiful place! I visited the island last summer and the water really is that clear. The early morning kayak trip around the bay was the highlight of the whole holiday, thanks for bringing back the memories.', 'created_at' => '2025-07-15 09:12:00']);
        Comment::create(['post_id' => $croatia->id, 'user_id' => $users['mary']->id, 'parent_id' => $c1->id, 'body' => 'Agreed! The kayak tour is a must. Did you also try the snorkeling spot near the lighthouse? It was even better than I expected.', 'created_at' => '2025-07-15 11:40:00']);
        Comment::create(['post_id' => $croatia->id, 'user_id' => $users['louis']->id, 'parent_id' => $c1->id, 'body' => 'Thank you both for reading! The snorkeling spot is on my list for the next article, so stay tuned for more tips about the island.', 'created_at' => '2025-07-16 08:05:00']);
        Comment::create(['post_id' => $croatia->id, 'user_id' => $users['patricia']->id, 'body' => 'This article convinced me to book a trip for next spring. Do you have any recommendations for a quiet bed and breakfast close to the beach?', 'created_at' => '2025-07-17 19:30:00']);

        $latest = [
            ['denis', 0, 'It was a great match today Janzi! You did great😉🇩🇪'],
            ['amanda', 0, 'You were stunning today, Jan! 💗 Great match 👍🏽👍🏽'],
            ['cassia', 13, "Take a rest, i'll be cheer up you again in 2 next game go go go"],
            ['ellsmartx', 5, 'How nice does this look 😍 I feel it should be delicious, thank you'],
        ];
        foreach ($latest as $i => [$user, $postIndex, $body]) {
            Comment::create([
                'post_id' => $posts[$postIndex]->id,
                'user_id' => $users[$user]->id,
                'body' => $body,
                'created_at' => now()->subHours(($i + 1) * 3),
            ]);
        }

        /* ---------- Bookmark ---------- */
        $users['daffa']->bookmarks()->attach($posts->except([3, 4, 13, 19])->take(12)->pluck('id'));
        $users['louis']->bookmarks()->attach($posts->take(4)->pluck('id'));
    }
}
