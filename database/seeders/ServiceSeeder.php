<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Painting services with typical starting prices. These are planning estimates; set real prices before launch.
     */
    public function run(): void
    {
        $rooms = [
            ['label' => 'Small room (under 120 sq ft)', 'multiplier' => 0.8],
            ['label' => 'Medium room (120–200 sq ft)', 'multiplier' => 1.0],
            ['label' => 'Large room (200–350 sq ft)', 'multiplier' => 1.45],
            ['label' => '2 rooms', 'multiplier' => 1.9],
            ['label' => '3 rooms', 'multiplier' => 2.7],
        ];
        $homes = [
            ['label' => 'Under 1,200 sq ft', 'multiplier' => 0.75],
            ['label' => '1,200–2,000 sq ft', 'multiplier' => 1.0],
            ['label' => '2,000–3,000 sq ft', 'multiplier' => 1.4],
            ['label' => 'Over 3,000 sq ft', 'multiplier' => 1.9],
        ];
        $exterior = [
            ['label' => '1 story, under 1,500 sq ft', 'multiplier' => 0.8],
            ['label' => '2 story, 1,500–2,500 sq ft', 'multiplier' => 1.0],
            ['label' => '2+ story, over 2,500 sq ft', 'multiplier' => 1.5],
        ];
        $cabinets = [
            ['label' => 'Under 20 doors & drawers', 'multiplier' => 0.75],
            ['label' => '20–35 doors & drawers', 'multiplier' => 1.0],
            ['label' => 'Over 35 doors & drawers', 'multiplier' => 1.4],
        ];
        $generic = [
            ['label' => 'Small', 'multiplier' => 0.8],
            ['label' => 'Medium', 'multiplier' => 1.0],
            ['label' => 'Large', 'multiplier' => 1.5],
        ];

        $services = [
            ['interior-room-painting', 'Interior room', 'Interior', 'Walls in one bedroom, office or living room. Two coats.', 450, 'room', 'Sage · eggshell', '#C9D6C1', $rooms],
            ['whole-home-interior-painting', 'Whole-home interior', 'Interior', 'Walls throughout a typical 3-bedroom home.', 3800, 'home', 'Wheat · matte', '#E9D9B8', $homes],
            ['ceiling-painting', 'Ceilings', 'Interior', 'Flat ceiling white, with small crack repairs.', 350, 'room', 'Ceiling white · flat', '#B9C7D6', $rooms],
            ['accent-wall-painting', 'Accent wall', 'Interior', 'One feature wall with crisp tape lines.', 225, 'wall', 'Slate · satin', '#3E4B5A', $generic],
            ['trim-and-door-painting', 'Trim, doors & baseboards', 'Interior', 'Caulk, sand and semi-gloss on trim and doors.', 300, 'room', 'White · semi-gloss', '#B9C7D6', $rooms],
            ['cabinet-painting', 'Kitchen cabinets', 'Interior', 'Degrease, sand, prime and spray doors and boxes.', 3200, 'kitchen', 'Olive · satin', '#C9D6C1', $cabinets],
            ['drywall-repair-and-paint', 'Drywall repair & paint', 'Interior', 'Patch holes and dents, match texture, spot paint.', 250, 'repair', 'Patch & prime', '#E9D9B8', $generic],
            ['wallpaper-removal', 'Wallpaper removal', 'Interior', 'Strip, wash, skim coat and prime for paint.', 600, 'room', 'Prep', '#D8C1BC', $rooms],
            ['exterior-house-painting', 'Exterior house', 'Exterior', 'Siding and trim: scrape, prime bare wood, two coats.', 4200, 'home', 'Harbor blue · satin', '#B9C7D6', $exterior],
            ['exterior-trim-painting', 'Exterior trim & doors', 'Exterior', 'Fascia, window trim and front door refresh.', 900, 'home', 'Brick red · gloss', '#D8C1BC', $exterior],
            ['deck-staining', 'Deck staining', 'Exterior', 'Wash, brighten and semi-transparent stain.', 650, 'deck', 'Cedar · semi-transparent', '#E9D9B8', $generic],
            ['fence-staining', 'Fence staining', 'Exterior', 'Both sides, sprayed and back-brushed.', 550, 'fence', 'Redwood · solid', '#D8C1BC', $generic],
            ['pressure-washing-prep', 'Pressure wash & prep', 'Exterior', 'Wash siding before painting, with mildew treatment.', 300, 'home', 'Prep', '#C9D6C1', $exterior],
            ['garage-floor-epoxy', 'Garage floor epoxy', 'Specialty & commercial', 'Grind, two-part epoxy, flake broadcast and clear coat.', 1800, 'garage', 'Graphite flake', '#3E4B5A', $generic],
            ['commercial-interior-painting', 'Commercial interior', 'Specialty & commercial', 'Offices and retail, with after-hours scheduling.', 2500, 'space', 'Office grey · eggshell', '#B9C7D6', $generic],
            ['murals-and-decorative-finishes', 'Murals & decorative', 'Specialty & commercial', 'Hand-painted murals, limewash and faux finishes.', 800, 'wall', 'Limewash', '#D8C1BC', $generic],
        ];

        foreach ($services as $i => [$slug, $name, $category, $description, $price, $unit, $swatch, $color, $sizes]) {
            Service::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'starting_price_cents' => $price * 100,
                'unit' => $unit,
                'swatch_label' => $swatch,
                'swatch_color' => $color,
                'size_options' => $sizes,
                'sort_order' => $i,
            ]);
        }
    }
}
