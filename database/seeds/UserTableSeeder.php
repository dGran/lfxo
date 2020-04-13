<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use App\VerifyUser;
use App\Profile;

use App\TeamCategory;
use App\Team;

use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// $role_user = Role::where('name', 'user')->first();
  //       $role_admin = Role::where('name', 'admin')->first();

        // $user = User::create([
        //     'name' => 'LPX',
        //     'email' => 'lpx@example.com',
        //     'password' => bcrypt('secret'),
        //     'verified' => 1
        // ]);
        // $user->save();
        // $user->roles()->attach($role_admin);

        // $verifyUser = VerifyUser::create([
        //     'user_id' => $user->id,
        //     'token' => str_random(40)
        // ]);

        // $profile = Profile::create([
        //     'user_id' => $user->id,
        //     'avatar' => 'img/avatars/admin.png'
        // ]);

        // $faker = Faker::create();
        // foreach (range(1,40) as $index) {
        //     $user = User::create([
        //         'name' => 'user_'.$index,
        //         'email' => 'user_'.$index.'@example.com',
        //         'password' => bcrypt('secret'),
        //         'verified' => 1,
        //     ]);
        //     $user->roles()->attach($role_user);
        //     $verifyUser = VerifyUser::create([
        //         'user_id' => $user->id,
        //         'token' => str_random(40)
        //     ]);
        //     $profile = Profile::create([
        //         'user_id' => $user->id,
        //         'avatar' => 'img/avatars/gallery/' . $index . '.png'
        //     ]);
        // }


        // $category = TeamCategory::create([
        //     'name' => 'Packs',
        //     'slug' => str_slug('Packs')
        // ]);

        // foreach (range(1,32) as $index) {
        //     if ($index < 10) {
        //         $pack_num = '0' . $index;
        //     } else {
        //         $pack_num = $index;
        //     }
        //     $pack = Team::create([
        //         'team_category_id' => 1,
        //         'name' => "Pack $pack_num",
        //         'logo' => 'img/teams/packs.png',
        //         'slug' => str_slug("Pack $pack_num")
        //     ]);
        // }
    }
}