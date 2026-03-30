<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'user@demo.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        $user->profile()->updateOrCreate([], [
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ]);

        $moreUsers = collect([
            ['name' => 'Sara Vegan', 'email' => 'sara@demo.test', 'dietary_tags' => ['vegan']],
            ['name' => 'Omar No Sugar', 'email' => 'omar@demo.test', 'dietary_tags' => ['no_sugar']],
            ['name' => 'Mina Gluten Free', 'email' => 'mina@demo.test', 'dietary_tags' => ['gluten_free']],
            ['name' => 'Youssef No Lactose', 'email' => 'youssef@demo.test', 'dietary_tags' => ['no_lactose']],
            ['name' => 'Nora No Cholesterol', 'email' => 'nora@demo.test', 'dietary_tags' => ['no_cholesterol']],
            ['name' => 'Adam Balanced', 'email' => 'adam@demo.test', 'dietary_tags' => []],
            ['name' => 'Lina Vegan GF', 'email' => 'lina@demo.test', 'dietary_tags' => ['vegan', 'gluten_free']],
            ['name' => 'Ilyas No Sugar No Lactose', 'email' => 'ilyas@demo.test', 'dietary_tags' => ['no_sugar', 'no_lactose']],
        ])->map(function (array $data) {
            /** @var User $u */
            $u = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]
            );

            $u->profile()->updateOrCreate([], [
                'dietary_tags' => $data['dietary_tags'],
            ]);

            return $u;
        });

        $categories = collect([
            ['name' => 'Entrées', 'description' => 'Starters and light dishes', 'color' => '#60A5FA'],
            ['name' => 'Plats Principaux', 'description' => 'Main courses', 'color' => '#A78BFA'],
            ['name' => 'Desserts', 'description' => 'Sweet finishes', 'color' => '#FB7185'],
            ['name' => 'Boissons', 'description' => 'Drinks', 'color' => '#34D399'],
            ['name' => 'Végétarien', 'description' => 'Vegetarian friendly', 'color' => '#FBBF24'],
            ['name' => 'Sans Gluten', 'description' => 'Gluten-free choices', 'color' => '#F97316'],
        ])->map(function (array $data) use ($admin) {
            return Category::query()->firstOrCreate(
                ['user_id' => $admin->id, 'name' => $data['name']],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        })->keyBy('name');

        $ingredients = collect([
            ['name' => 'Chicken', 'tags' => ['contains_meat', 'contains_cholesterol']],
            ['name' => 'Beef', 'tags' => ['contains_meat', 'contains_cholesterol']],
            ['name' => 'Fish', 'tags' => ['contains_meat', 'contains_cholesterol']],
            ['name' => 'Flour', 'tags' => ['contains_gluten']],
            ['name' => 'Bread', 'tags' => ['contains_gluten']],
            ['name' => 'Cheese', 'tags' => ['contains_lactose', 'contains_cholesterol']],
            ['name' => 'Milk', 'tags' => ['contains_lactose', 'contains_cholesterol']],
            ['name' => 'Butter', 'tags' => ['contains_lactose', 'contains_cholesterol']],
            ['name' => 'Eggs', 'tags' => ['contains_cholesterol']],
            ['name' => 'Sugar', 'tags' => ['contains_sugar']],
            ['name' => 'Honey', 'tags' => ['contains_sugar']],
            ['name' => 'Lettuce', 'tags' => []],
            ['name' => 'Tomato', 'tags' => []],
            ['name' => 'Potato', 'tags' => []],
            ['name' => 'Rice', 'tags' => []],
            ['name' => 'Tofu', 'tags' => []],
            ['name' => 'Olive Oil', 'tags' => []],
            ['name' => 'Lemon', 'tags' => []],
        ])->map(function (array $data) {
            return Ingredient::query()->firstOrCreate(
                ['name' => $data['name']],
                [
                    'tags' => $data['tags'],
                ]
            );
        })->keyBy('name');

        $plateA = Plate::query()->updateOrCreate(
            ['user_id' => $admin->id, 'name' => 'Chicken Caesar Salad'],
            [
                'description' => 'Classic Caesar with chicken and parmesan.',
                'price' => 9.90,
                'image' => null,
                'is_available' => true,
                'category_id' => $categories['Entrées']->id,
                'user_id' => $admin->id,
            ]
        );
        $plateA->ingredients()->syncWithoutDetaching([
            $ingredients['Chicken']->id,
            $ingredients['Cheese']->id,
            $ingredients['Lettuce']->id,
            $ingredients['Olive Oil']->id,
        ]);

        $plateB = Plate::query()->updateOrCreate(
            ['user_id' => $admin->id, 'name' => 'Vegan Buddha Bowl'],
            [
                'description' => 'Fresh vegetables with olive oil.',
                'price' => 11.50,
                'image' => null,
                'is_available' => true,
                'category_id' => $categories['Végétarien']->id,
                'user_id' => $admin->id,
            ]
        );
        $plateB->ingredients()->syncWithoutDetaching([
            $ingredients['Lettuce']->id,
            $ingredients['Tomato']->id,
            $ingredients['Olive Oil']->id,
        ]);

        $plateC = Plate::query()->updateOrCreate(
            ['user_id' => $admin->id, 'name' => 'Chocolate Brownie'],
            [
                'description' => 'Rich brownie (contains gluten and sugar).',
                'price' => 6.00,
                'image' => null,
                'is_available' => true,
                'category_id' => $categories['Desserts']->id,
                'user_id' => $admin->id,
            ]
        );
        $plateC->ingredients()->syncWithoutDetaching([
            $ingredients['Flour']->id,
            $ingredients['Sugar']->id,
        ]);

        $morePlates = [
            [
                'name' => 'Grilled Salmon & Rice',
                'description' => 'Salmon with steamed rice, lemon and olive oil.',
                'price' => 14.90,
                'category' => 'Plats Principaux',
                'ingredients' => ['Fish', 'Rice', 'Lemon', 'Olive Oil'],
            ],
            [
                'name' => 'Beef Burger',
                'description' => 'Beef burger with bread and cheese.',
                'price' => 12.50,
                'category' => 'Plats Principaux',
                'ingredients' => ['Beef', 'Bread', 'Cheese', 'Tomato'],
            ],
            [
                'name' => 'Mashed Potatoes',
                'description' => 'Potatoes with butter and milk.',
                'price' => 5.90,
                'category' => 'EntrÃ©es',
                'ingredients' => ['Potato', 'Butter', 'Milk'],
            ],
            [
                'name' => 'Tofu Salad',
                'description' => 'Tofu with lettuce, tomato and olive oil.',
                'price' => 10.20,
                'category' => 'VÃ©gÃ©tarien',
                'ingredients' => ['Tofu', 'Lettuce', 'Tomato', 'Olive Oil'],
            ],
            [
                'name' => 'Honey Lemon Drink',
                'description' => 'Refreshing drink with honey and lemon.',
                'price' => 3.50,
                'category' => 'Boissons',
                'ingredients' => ['Honey', 'Lemon'],
            ],
            [
                'name' => 'Omelette',
                'description' => 'Egg omelette with cheese.',
                'price' => 7.20,
                'category' => 'Plats Principaux',
                'ingredients' => ['Eggs', 'Cheese', 'Olive Oil'],
            ],
        ];

        foreach ($morePlates as $data) {
            $category = $categories[$data['category']] ?? $categories->first();
            if (!$category) {
                continue;
            }

            $plate = Plate::query()->updateOrCreate(
                ['user_id' => $admin->id, 'name' => $data['name']],
                [
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'image' => null,
                    'is_available' => true,
                    'category_id' => $category->id,
                ]
            );

            $ingredientIds = collect($data['ingredients'])
                ->map(fn (string $name) => $ingredients[$name]->id ?? null)
                ->filter()
                ->values()
                ->all();

            if (count($ingredientIds) > 0) {
                $plate->ingredients()->syncWithoutDetaching($ingredientIds);
            }
        }

        // Seed a set of ready recommendations for demo/testing.
        $rules = [
            'vegan' => 'contains_meat',
            'no_sugar' => 'contains_sugar',
            'no_cholesterol' => 'contains_cholesterol',
            'gluten_free' => 'contains_gluten',
            'no_lactose' => 'contains_lactose',
        ];

        $allUsers = collect([$user])->concat($moreUsers)->values();
        $allPlates = Plate::query()
            ->where('user_id', $admin->id)
            ->with('ingredients')
            ->orderBy('id')
            ->get()
            ->values();

        if ($allPlates->count() > 0) {
            foreach ($allUsers as $userIndex => $u) {
                /** @var array<int, string> $dietaryTags */
                $dietaryTags = $u->profile?->dietary_tags ?? [];
                $dietaryTags = is_array($dietaryTags) ? array_values(array_unique($dietaryTags)) : [];

                $perUser = 5;
                for ($i = 0; $i < min($perUser, $allPlates->count()); $i++) {
                    $plate = $allPlates[($userIndex + $i) % $allPlates->count()];

                    $ingredientTags = $plate->ingredients
                        ->flatMap(fn ($ingredient) => $ingredient->tags ?? [])
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $conflicts = [];
                    foreach ($dietaryTags as $dietaryTag) {
                        $conflictingIngredientTag = $rules[$dietaryTag] ?? null;
                        if (!$conflictingIngredientTag) {
                            continue;
                        }

                        if (in_array($conflictingIngredientTag, $ingredientTags, true)) {
                            $conflicts[] = [
                                'dietary_tag' => $dietaryTag,
                                'ingredient_tag' => $conflictingIngredientTag,
                            ];
                        }
                    }

                    $score = 100 - (count($conflicts) * 20);
                    $score = max(0, min(100, $score));

                    $label = match (true) {
                        $score >= 80 => 'Highly Recommended',
                        $score >= 50 => 'Recommended with notes',
                        default => 'Not Recommended',
                    };

                    $warningMessage = null;
                    if (count($conflicts) > 0) {
                        $pairs = collect($conflicts)
                            ->map(fn ($c) => "{$c['dietary_tag']} ({$c['ingredient_tag']})")
                            ->implode(', ');

                        $warningMessage = $score < 50
                            ? "Not compatible with your dietary profile: {$pairs}."
                            : "Notes: {$pairs}.";
                    }

                    Recommendation::query()->firstOrCreate(
                        [
                            'user_id' => $u->id,
                            'plate_id' => $plate->id,
                        ],
                        [
                            'status' => 'ready',
                            'score' => $score,
                            'label' => $label,
                            'warning_message' => $warningMessage,
                        ]
                    );
                }
            }
        }
    }
}
