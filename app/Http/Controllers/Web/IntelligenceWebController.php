<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IntelligenceWebController extends Controller
{
    public function valuation()
    {
        $estimate = [
            'value' => '8,500,000 ETB',
            'range' => '8.2M - 8.9M ETB',
            'confidence' => 'High',
            'comparables' => [
                ['id' => 'C1', 'address' => 'Bole Medhanialem Apt 304', 'price' => '8,600,000 ETB', 'beds' => 3, 'baths' => 2, 'sqm' => 120, 'date' => '2 weeks ago'],
                ['id' => 'C2', 'address' => 'Bole Homes Villa', 'price' => '8,400,000 ETB', 'beds' => 3, 'baths' => 2, 'sqm' => 115, 'date' => '1 month ago'],
                ['id' => 'C3', 'address' => 'CMC Luxury Condominium', 'price' => '8,800,000 ETB', 'beds' => 3, 'baths' => 3, 'sqm' => 130, 'date' => '3 days ago'],
            ]
        ];

        return view('frontend.intelligence.valuation', compact('estimate'));
    }

    public function calculators()
    {
        $mortgage = [
            'principal' => 8500000,
            'down_payment' => 1700000, // 20%
            'interest_rate' => 14.5,
            'years' => 20,
            'monthly_payment' => '94,250 ETB'
        ];

        $investment = [
            'purchase_price' => 8500000,
            'monthly_rent' => 60000,
            'annual_expenses' => 120000,
            'cap_rate' => '7.06%',
            'cash_on_cash' => '12.4%',
            'gross_yield' => '8.47%'
        ];

        return view('frontend.intelligence.calculators', compact('mortgage', 'investment'));
    }

    public function portfolio()
    {
        $portfolio = [
            'total_value' => '24,500,000 ETB',
            'total_equity' => '16,200,000 ETB',
            'monthly_cashflow' => '145,000 ETB',
            'properties' => [
                ['name' => 'Bole Apartment', 'value' => '8,500,000 ETB', 'equity' => '4,000,000 ETB', 'roi' => '8.2%'],
                ['name' => 'Kazanchis Office', 'value' => '12,000,000 ETB', 'equity' => '10,000,000 ETB', 'roi' => '6.5%'],
                ['name' => 'CMC Studio', 'value' => '4,000,000 ETB', 'equity' => '2,200,000 ETB', 'roi' => '9.1%'],
            ]
        ];

        return view('frontend.intelligence.portfolio', compact('portfolio'));
    }
}
