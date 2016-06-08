<?php

use Illuminate\Database\Seeder;
use App\Poem;

class PoemsTableSeeder extends Seeder
{
    public function run()
    {

    	$faker = Faker\Factory::create();

		foreach (range(1, 30) as $index) {
			Poem::create([
					'body'		=> 	$faker->paragraph($nbSentences = 10),
					'user_id'	=>	$faker->numberBetween($min = 1, $max = 5)		
				]);
		}

    }
}
