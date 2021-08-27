<?php


namespace App\Http\Controllers;

use App\Http\Middleware\Calculator;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function calculate(Request $request){
        if($request->avg_days == null){
            $request->avg_days = 22;
        }
        return "ЗП после расчета: " . Calculator::calculate(
                $request->salary,
                $request->avg_days,
                $request->worked_days,
                $request->is_tax,
                $request->year,
                $request->month,
                $request->is_retiree,
                $request->disabled)
            . " тг";
    }

    public function save(Request $request){
        return Calculator::save(
            $request->salary,
            $request->avg_days,
            $request->worked_days,
            $request->is_tax,
            $request->year,
            $request->month,
            $request->is_retiree,
            $request->disabled
        );
    }
}
