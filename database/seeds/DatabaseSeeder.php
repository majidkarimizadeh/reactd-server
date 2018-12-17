<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
    	$user = User::create([
    		'email'	=>	'superuser@reactd.com',
    		'password'	=>	bcrypt('123456')
    	]);

		$role = Role::create([
			'name'	=>	'Owner',
			'slug'	=>	'owner'
		]);

		$user->roles()->attach($role);
    }
}
