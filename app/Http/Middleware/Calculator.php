<?php


namespace App\Http\Middleware;

use App\Models\Salary;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Calculator extends Middleware
{
    private static $mrp = 2778;
    private static $mzp = 42500;

    static function calculate($salary, $avg_days, $worked_days, $is_tax, $year, $month, $is_retiree, $disabled = null){
        $opv = $salary * 0.1;
        $vosms = $salary * 0.02;
        $osms = $salary * 0.02;
        $so = ($salary - $opv) * 0.035;

        $ipn = $opv + $vosms;
        $correcting = $salary - $opv - $vosms;

        if($is_tax){
            $ipn += self::$mzp;
            $correcting += self::$mzp;
        }

        $correcting *= 0.9;

        if($salary < 25 * self::$mrp){
            $ipn += $correcting * 0.1;
        }

        $sum = $salary;

        if($disabled){
            if($salary > 882 * self::$mrp){
                $sum -= $ipn;
            }

            if(($disabled == 1 || $disabled == 2) && !$is_retiree){
                $sum -= $so;
            }
            else if($disabled == 3 && !$is_retiree){
                $sum -= $opv;
                $sum -= $so;
            }
        }
        else if($is_retiree){
            $sum -= $ipn;
        }
        else{
            $sum -= ($ipn + $opv + $osms + $vosms + $so);
        }

        return $sum;
    }

    static function save(...$params){
        $model = new Salary();
        $model->paid = self::calculate(...$params);
        $model->avg_days = $params[1];
        $model->worked_days = $params[2];
        $model->year = $params[4];
        $model->month = $params[5];
        $model->save();

        $taxes = array();
        $salary = $params[0];
        $is_retiree = $params[6];
        $disabled = $params[7];

        if($disabled){
            if($salary > 882 * self::$mrp){
                $taxes[] = "IPN";
            }
            if(($disabled == 1 || $disabled == 2) && !$is_retiree){
                $taxes[] = "SO";
            }
            else if($disabled == 3 && !$is_retiree){
                $taxes[] = "OPV";
                $taxes[] = "SO";
            }
        }
        else if ($is_retiree){
            $taxes[] = "IPN";
        }
        else{
            $taxes[] = "IPN";
            $taxes[] = "OPV";
            $taxes[] = "OSMS";
            $taxes[] = "VOSMS";
            $taxes[] = "SO";
        }
        return ["taxes" => $taxes, "salary_with_taxes" => $model->paid, "paid_salary" => $model->paid];
    }
}
