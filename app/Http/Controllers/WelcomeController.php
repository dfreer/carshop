<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Car;

class WelcomeController extends Controller
{
    private $sortableColumns = [
        'make',
        'model',
        'year',
        'condition',
        'price',
        'status',
        'seller',
    ];

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'sort' => ['nullable', Rule::in($this->sortableColumns)],
            'make' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
        ]);

        $sort = $request->get('sort', reset($this->sortableColumns));
        $make = $request->get('make');
        $model = $request->get('model');

        $cars = Car::when($make, function ($query, $make) {
            $query->where('make', $make);
        })
            ->when($model, function ($query, $model) {
                $query->where('model', $model);
            })
            ->orderBy($sort)
            ->simplePaginate();

        return view('welcome', [
            'sortableColumns' => collect($this->sortableColumns)
                ->map(function ($column) {
                    return (object) [
                        'value' => $column,
                        'label' => ucfirst($column),
                    ];
                }),
            'makes' => Car::select('make')->distinct()->get()->pluck('make'),
            'models' => Car::select('model')->distinct()->get()->pluck('model'),
            'request' => (object) [
                'make' => $make,
                'model' => $model,
                'sort' => $sort,
            ],
            'cars' => $cars,
        ]);
    }
}
