<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Utility;

use function array_combine;
use function array_keys;
use function array_map;
use function array_sum;
use function count;
use function log;
use function min;
use function pow;
use function sqrt;

class Math
{
    public static function logTransformScalar($x, $min): float
    {
        return log($x - ($min - 1));
    }

    public static function logTransform(array $values, bool $preserveKeys = false): array
    {
        $min = min($values);
        $transformed = array_map(function ($x) use ($min): float {
            return log($x - ($min - 1));
        }, $values);
        if ($preserveKeys) {
            $transformed = array_combine(array_keys($values), $transformed) ?: [];
        }
        return $transformed;
    }

    public static function groupStDev(array $groups): float
    {
        $mean = self::groupMean($groups);
        $frequencies = array_keys($groups);
        $squaresByFrequency = array_map(function ($x, int $frequency) use ($mean): float {
            return (float)(pow($x - $mean, 2) * (float)$frequency);
        }, $groups, $frequencies);
        return sqrt(array_sum($squaresByFrequency) / array_sum($frequencies));
    }

    public static function groupMean(array $groups): float
    {
        $frequencies = array_keys($groups);
        $sumsByFrequency = array_map(function ($x, int $frequency): float {
            return (float)($x * (float)$frequency);
        }, $groups, $frequencies);
        return (float)(array_sum($sumsByFrequency) / array_sum($frequencies));
    }

    public static function stDev(array $values): float
    {
        $mean = self::mean($values);
        $squares = array_map(function ($x) use ($mean): float {
            return pow($x - $mean, 2);
        }, $values);
        return sqrt(self::mean($squares));
    }

    public static function mean(array $values): float
    {
        if (0 === count($values)) {
            return 0.0;
        }

        return (float)(array_sum($values) / count($values));
    }

    public static function standardize(float $x, float $mean, float $stDev): float
    {
        return self::safeDivision($x - $mean, $stDev);
    }

    /**
     * @param float|int $dividend
     * @param float|int $divisor
     * @return float
     */
    public static function safeDivision($dividend, $divisor): float
    {
        if (0 === $divisor || 0.0 === $divisor) {
            return 1.0;
        }

        return (float)($dividend / $divisor);
    }

    public static function percentile(float $z): float
    {
        // too hard to calculate improper integrals in PHP. Let's pretend this is good code
        switch ($z) {
            case $z < -3.900:
                return 0.0;
            case $z < -3.890:
                return 0.000050;
            case $z < -3.880:
                return 0.000052;
            case $z < -3.870:
                return 0.000054;
            case $z < -3.860:
                return 0.000057;
            case $z < -3.850:
                return 0.000059;
            case $z < -3.840:
                return 0.000062;
            case $z < -3.830:
                return 0.000064;
            case $z < -3.820:
                return 0.000067;
            case $z < -3.810:
                return 0.000069;
            case $z < -3.800:
                return 0.000072;
            case $z < -3.790:
                return 0.000075;
            case $z < -3.780:
                return 0.000078;
            case $z < -3.770:
                return 0.000082;
            case $z < -3.760:
                return 0.000085;
            case $z < -3.750:
                return 0.000088;
            case $z < -3.740:
                return 0.000092;
            case $z < -3.730:
                return 0.000096;
            case $z < -3.720:
                return 0.000100;
            case $z < -3.710:
                return 0.000104;
            case $z < -3.700:
                return 0.000108;
            case $z < -3.690:
                return 0.000112;
            case $z < -3.680:
                return 0.000117;
            case $z < -3.670:
                return 0.000121;
            case $z < -3.660:
                return 0.000126;
            case $z < -3.650:
                return 0.000131;
            case $z < -3.640:
                return 0.000136;
            case $z < -3.630:
                return 0.000142;
            case $z < -3.620:
                return 0.000147;
            case $z < -3.610:
                return 0.000153;
            case $z < -3.600:
                return 0.000159;
            case $z < -3.590:
                return 0.000165;
            case $z < -3.580:
                return 0.000172;
            case $z < -3.570:
                return 0.000178;
            case $z < -3.560:
                return 0.000185;
            case $z < -3.550:
                return 0.000193;
            case $z < -3.540:
                return 0.000200;
            case $z < -3.530:
                return 0.000208;
            case $z < -3.520:
                return 0.000216;
            case $z < -3.510:
                return 0.000224;
            case $z < -3.500:
                return 0.000233;
            case $z < -3.490:
                return 0.000242;
            case $z < -3.480:
                return 0.000251;
            case $z < -3.470:
                return 0.000260;
            case $z < -3.460:
                return 0.000270;
            case $z < -3.450:
                return 0.000280;
            case $z < -3.440:
                return 0.000291;
            case $z < -3.430:
                return 0.000302;
            case $z < -3.420:
                return 0.000313;
            case $z < -3.410:
                return 0.000325;
            case $z < -3.400:
                return 0.000337;
            case $z < -3.390:
                return 0.000349;
            case $z < -3.380:
                return 0.000362;
            case $z < -3.370:
                return 0.000376;
            case $z < -3.360:
                return 0.000390;
            case $z < -3.350:
                return 0.000404;
            case $z < -3.340:
                return 0.000419;
            case $z < -3.330:
                return 0.000434;
            case $z < -3.320:
                return 0.000450;
            case $z < -3.310:
                return 0.000466;
            case $z < -3.300:
                return 0.000483;
            case $z < -3.290:
                return 0.000501;
            case $z < -3.280:
                return 0.000519;
            case $z < -3.270:
                return 0.000538;
            case $z < -3.260:
                return 0.000557;
            case $z < -3.250:
                return 0.000577;
            case $z < -3.240:
                return 0.000598;
            case $z < -3.230:
                return 0.000619;
            case $z < -3.220:
                return 0.000641;
            case $z < -3.210:
                return 0.000664;
            case $z < -3.200:
                return 0.000687;
            case $z < -3.190:
                return 0.000711;
            case $z < -3.180:
                return 0.000736;
            case $z < -3.170:
                return 0.000762;
            case $z < -3.160:
                return 0.000789;
            case $z < -3.150:
                return 0.000816;
            case $z < -3.140:
                return 0.000845;
            case $z < -3.130:
                return 0.000874;
            case $z < -3.120:
                return 0.000904;
            case $z < -3.110:
                return 0.000935;
            case $z < -3.100:
                return 0.000968;
            case $z < -3.090:
                return 0.001001;
            case $z < -3.080:
                return 0.001035;
            case $z < -3.070:
                return 0.001070;
            case $z < -3.060:
                return 0.001107;
            case $z < -3.050:
                return 0.001144;
            case $z < -3.040:
                return 0.001183;
            case $z < -3.030:
                return 0.001223;
            case $z < -3.020:
                return 0.001264;
            case $z < -3.010:
                return 0.001306;
            case $z < -3.000:
                return 0.001350;
            case $z < -2.990:
                return 0.001395;
            case $z < -2.980:
                return 0.001441;
            case $z < -2.970:
                return 0.001489;
            case $z < -2.960:
                return 0.001538;
            case $z < -2.950:
                return 0.001589;
            case $z < -2.940:
                return 0.001641;
            case $z < -2.930:
                return 0.001695;
            case $z < -2.920:
                return 0.001750;
            case $z < -2.910:
                return 0.001807;
            case $z < -2.900:
                return 0.001866;
            case $z < -2.890:
                return 0.001926;
            case $z < -2.880:
                return 0.001988;
            case $z < -2.870:
                return 0.002052;
            case $z < -2.860:
                return 0.002118;
            case $z < -2.850:
                return 0.002186;
            case $z < -2.840:
                return 0.002256;
            case $z < -2.830:
                return 0.002327;
            case $z < -2.820:
                return 0.002401;
            case $z < -2.810:
                return 0.002477;
            case $z < -2.800:
                return 0.002555;
            case $z < -2.790:
                return 0.002635;
            case $z < -2.780:
                return 0.002718;
            case $z < -2.770:
                return 0.002803;
            case $z < -2.760:
                return 0.002890;
            case $z < -2.750:
                return 0.002980;
            case $z < -2.740:
                return 0.003072;
            case $z < -2.730:
                return 0.003167;
            case $z < -2.720:
                return 0.003264;
            case $z < -2.710:
                return 0.003364;
            case $z < -2.700:
                return 0.003467;
            case $z < -2.690:
                return 0.003573;
            case $z < -2.680:
                return 0.003681;
            case $z < -2.670:
                return 0.003793;
            case $z < -2.660:
                return 0.003907;
            case $z < -2.650:
                return 0.004025;
            case $z < -2.640:
                return 0.004145;
            case $z < -2.630:
                return 0.004269;
            case $z < -2.620:
                return 0.004396;
            case $z < -2.610:
                return 0.004527;
            case $z < -2.600:
                return 0.004661;
            case $z < -2.590:
                return 0.004799;
            case $z < -2.580:
                return 0.004940;
            case $z < -2.570:
                return 0.005085;
            case $z < -2.560:
                return 0.005234;
            case $z < -2.550:
                return 0.005386;
            case $z < -2.540:
                return 0.005543;
            case $z < -2.530:
                return 0.005703;
            case $z < -2.520:
                return 0.005868;
            case $z < -2.510:
                return 0.006037;
            case $z < -2.500:
                return 0.006210;
            case $z < -2.490:
                return 0.006387;
            case $z < -2.480:
                return 0.006569;
            case $z < -2.470:
                return 0.006756;
            case $z < -2.460:
                return 0.006947;
            case $z < -2.450:
                return 0.007143;
            case $z < -2.440:
                return 0.007344;
            case $z < -2.430:
                return 0.007549;
            case $z < -2.420:
                return 0.007760;
            case $z < -2.410:
                return 0.007976;
            case $z < -2.400:
                return 0.008198;
            case $z < -2.390:
                return 0.008424;
            case $z < -2.380:
                return 0.008656;
            case $z < -2.370:
                return 0.008894;
            case $z < -2.360:
                return 0.009137;
            case $z < -2.350:
                return 0.009387;
            case $z < -2.340:
                return 0.009642;
            case $z < -2.330:
                return 0.009903;
            case $z < -2.320:
                return 0.010170;
            case $z < -2.310:
                return 0.010444;
            case $z < -2.300:
                return 0.010724;
            case $z < -2.290:
                return 0.011011;
            case $z < -2.280:
                return 0.011304;
            case $z < -2.270:
                return 0.011604;
            case $z < -2.260:
                return 0.011911;
            case $z < -2.250:
                return 0.012224;
            case $z < -2.240:
                return 0.012545;
            case $z < -2.230:
                return 0.012874;
            case $z < -2.220:
                return 0.013209;
            case $z < -2.210:
                return 0.013553;
            case $z < -2.200:
                return 0.013903;
            case $z < -2.190:
                return 0.014262;
            case $z < -2.180:
                return 0.014629;
            case $z < -2.170:
                return 0.015003;
            case $z < -2.160:
                return 0.015386;
            case $z < -2.150:
                return 0.015778;
            case $z < -2.140:
                return 0.016177;
            case $z < -2.130:
                return 0.016586;
            case $z < -2.120:
                return 0.017003;
            case $z < -2.110:
                return 0.017429;
            case $z < -2.100:
                return 0.017864;
            case $z < -2.090:
                return 0.018309;
            case $z < -2.080:
                return 0.018763;
            case $z < -2.070:
                return 0.019226;
            case $z < -2.060:
                return 0.019699;
            case $z < -2.050:
                return 0.020182;
            case $z < -2.040:
                return 0.020675;
            case $z < -2.030:
                return 0.021178;
            case $z < -2.020:
                return 0.021692;
            case $z < -2.010:
                return 0.022216;
            case $z < -2.000:
                return 0.022750;
            case $z < -1.990:
                return 0.023295;
            case $z < -1.980:
                return 0.023852;
            case $z < -1.970:
                return 0.024419;
            case $z < -1.960:
                return 0.024998;
            case $z < -1.950:
                return 0.025588;
            case $z < -1.940:
                return 0.026190;
            case $z < -1.930:
                return 0.026803;
            case $z < -1.920:
                return 0.027429;
            case $z < -1.910:
                return 0.028067;
            case $z < -1.900:
                return 0.028717;
            case $z < -1.890:
                return 0.029379;
            case $z < -1.880:
                return 0.030054;
            case $z < -1.870:
                return 0.030742;
            case $z < -1.860:
                return 0.031443;
            case $z < -1.850:
                return 0.032157;
            case $z < -1.840:
                return 0.032884;
            case $z < -1.830:
                return 0.033625;
            case $z < -1.820:
                return 0.034380;
            case $z < -1.810:
                return 0.035148;
            case $z < -1.800:
                return 0.035930;
            case $z < -1.790:
                return 0.036727;
            case $z < -1.780:
                return 0.037538;
            case $z < -1.770:
                return 0.038364;
            case $z < -1.760:
                return 0.039204;
            case $z < -1.750:
                return 0.040059;
            case $z < -1.740:
                return 0.040930;
            case $z < -1.730:
                return 0.041815;
            case $z < -1.720:
                return 0.042716;
            case $z < -1.710:
                return 0.043633;
            case $z < -1.700:
                return 0.044565;
            case $z < -1.690:
                return 0.045514;
            case $z < -1.680:
                return 0.046479;
            case $z < -1.670:
                return 0.047460;
            case $z < -1.660:
                return 0.048457;
            case $z < -1.650:
                return 0.049471;
            case $z < -1.640:
                return 0.050503;
            case $z < -1.630:
                return 0.051551;
            case $z < -1.620:
                return 0.052616;
            case $z < -1.610:
                return 0.053699;
            case $z < -1.600:
                return 0.054799;
            case $z < -1.590:
                return 0.055917;
            case $z < -1.580:
                return 0.057053;
            case $z < -1.570:
                return 0.058208;
            case $z < -1.560:
                return 0.059380;
            case $z < -1.550:
                return 0.060571;
            case $z < -1.540:
                return 0.061780;
            case $z < -1.530:
                return 0.063008;
            case $z < -1.520:
                return 0.064255;
            case $z < -1.510:
                return 0.065522;
            case $z < -1.500:
                return 0.066807;
            case $z < -1.490:
                return 0.068112;
            case $z < -1.480:
                return 0.069437;
            case $z < -1.470:
                return 0.070781;
            case $z < -1.460:
                return 0.072145;
            case $z < -1.450:
                return 0.073529;
            case $z < -1.440:
                return 0.074934;
            case $z < -1.430:
                return 0.076359;
            case $z < -1.420:
                return 0.077804;
            case $z < -1.410:
                return 0.079270;
            case $z < -1.400:
                return 0.080757;
            case $z < -1.390:
                return 0.082264;
            case $z < -1.380:
                return 0.083793;
            case $z < -1.370:
                return 0.085343;
            case $z < -1.360:
                return 0.086915;
            case $z < -1.350:
                return 0.088508;
            case $z < -1.340:
                return 0.090123;
            case $z < -1.330:
                return 0.091759;
            case $z < -1.320:
                return 0.093418;
            case $z < -1.310:
                return 0.095098;
            case $z < -1.300:
                return 0.096800;
            case $z < -1.290:
                return 0.098525;
            case $z < -1.280:
                return 0.100273;
            case $z < -1.270:
                return 0.102042;
            case $z < -1.260:
                return 0.103835;
            case $z < -1.250:
                return 0.105650;
            case $z < -1.240:
                return 0.107488;
            case $z < -1.230:
                return 0.109349;
            case $z < -1.220:
                return 0.111232;
            case $z < -1.210:
                return 0.113139;
            case $z < -1.200:
                return 0.115070;
            case $z < -1.190:
                return 0.117023;
            case $z < -1.180:
                return 0.119000;
            case $z < -1.170:
                return 0.121000;
            case $z < -1.160:
                return 0.123024;
            case $z < -1.150:
                return 0.125072;
            case $z < -1.140:
                return 0.127143;
            case $z < -1.130:
                return 0.129238;
            case $z < -1.120:
                return 0.131357;
            case $z < -1.110:
                return 0.133500;
            case $z < -1.100:
                return 0.135666;
            case $z < -1.090:
                return 0.137857;
            case $z < -1.080:
                return 0.140071;
            case $z < -1.070:
                return 0.142310;
            case $z < -1.060:
                return 0.144572;
            case $z < -1.050:
                return 0.146859;
            case $z < -1.040:
                return 0.149170;
            case $z < -1.030:
                return 0.151505;
            case $z < -1.020:
                return 0.153864;
            case $z < -1.010:
                return 0.156248;
            case $z < -1.000:
                return 0.158655;
            case $z < -0.990:
                return 0.161087;
            case $z < -0.980:
                return 0.163543;
            case $z < -0.970:
                return 0.166023;
            case $z < -0.960:
                return 0.168528;
            case $z < -0.950:
                return 0.171056;
            case $z < -0.940:
                return 0.173609;
            case $z < -0.930:
                return 0.176186;
            case $z < -0.920:
                return 0.178786;
            case $z < -0.910:
                return 0.181411;
            case $z < -0.900:
                return 0.184060;
            case $z < -0.890:
                return 0.186733;
            case $z < -0.880:
                return 0.189430;
            case $z < -0.870:
                return 0.192150;
            case $z < -0.860:
                return 0.194895;
            case $z < -0.850:
                return 0.197663;
            case $z < -0.840:
                return 0.200454;
            case $z < -0.830:
                return 0.203269;
            case $z < -0.820:
                return 0.206108;
            case $z < -0.810:
                return 0.208970;
            case $z < -0.800:
                return 0.211855;
            case $z < -0.790:
                return 0.214764;
            case $z < -0.780:
                return 0.217695;
            case $z < -0.770:
                return 0.220650;
            case $z < -0.760:
                return 0.223627;
            case $z < -0.750:
                return 0.226627;
            case $z < -0.740:
                return 0.229650;
            case $z < -0.730:
                return 0.232695;
            case $z < -0.720:
                return 0.235762;
            case $z < -0.710:
                return 0.238852;
            case $z < -0.700:
                return 0.241964;
            case $z < -0.690:
                return 0.245097;
            case $z < -0.680:
                return 0.248252;
            case $z < -0.670:
                return 0.251429;
            case $z < -0.660:
                return 0.254627;
            case $z < -0.650:
                return 0.257846;
            case $z < -0.640:
                return 0.261086;
            case $z < -0.630:
                return 0.264347;
            case $z < -0.620:
                return 0.267629;
            case $z < -0.610:
                return 0.270931;
            case $z < -0.600:
                return 0.274253;
            case $z < -0.590:
                return 0.277595;
            case $z < -0.580:
                return 0.280957;
            case $z < -0.570:
                return 0.284339;
            case $z < -0.560:
                return 0.287740;
            case $z < -0.550:
                return 0.291160;
            case $z < -0.540:
                return 0.294599;
            case $z < -0.530:
                return 0.298056;
            case $z < -0.520:
                return 0.301532;
            case $z < -0.510:
                return 0.305026;
            case $z < -0.500:
                return 0.308538;
            case $z < -0.490:
                return 0.312067;
            case $z < -0.480:
                return 0.315614;
            case $z < -0.470:
                return 0.319178;
            case $z < -0.460:
                return 0.322758;
            case $z < -0.450:
                return 0.326355;
            case $z < -0.440:
                return 0.329969;
            case $z < -0.430:
                return 0.333598;
            case $z < -0.420:
                return 0.337243;
            case $z < -0.410:
                return 0.340903;
            case $z < -0.400:
                return 0.344578;
            case $z < -0.390:
                return 0.348268;
            case $z < -0.380:
                return 0.351973;
            case $z < -0.370:
                return 0.355691;
            case $z < -0.360:
                return 0.359424;
            case $z < -0.350:
                return 0.363169;
            case $z < -0.340:
                return 0.366928;
            case $z < -0.330:
                return 0.370700;
            case $z < -0.320:
                return 0.374484;
            case $z < -0.310:
                return 0.378280;
            case $z < -0.300:
                return 0.382089;
            case $z < -0.290:
                return 0.385908;
            case $z < -0.280:
                return 0.389739;
            case $z < -0.270:
                return 0.393580;
            case $z < -0.260:
                return 0.397432;
            case $z < -0.250:
                return 0.401294;
            case $z < -0.240:
                return 0.405165;
            case $z < -0.230:
                return 0.409046;
            case $z < -0.220:
                return 0.412936;
            case $z < -0.210:
                return 0.416834;
            case $z < -0.200:
                return 0.420740;
            case $z < -0.190:
                return 0.424655;
            case $z < -0.180:
                return 0.428576;
            case $z < -0.170:
                return 0.432505;
            case $z < -0.160:
                return 0.436441;
            case $z < -0.150:
                return 0.440382;
            case $z < -0.140:
                return 0.444330;
            case $z < -0.130:
                return 0.448283;
            case $z < -0.120:
                return 0.452242;
            case $z < -0.110:
                return 0.456205;
            case $z < -0.100:
                return 0.460172;
            case $z < -0.090:
                return 0.464144;
            case $z < -0.080:
                return 0.468119;
            case $z < -0.070:
                return 0.472097;
            case $z < -0.060:
                return 0.476078;
            case $z < -0.050:
                return 0.480061;
            case $z < -0.040:
                return 0.484047;
            case $z < -0.030:
                return 0.488034;
            case $z < -0.020:
                return 0.492022;
            case $z < -0.010:
                return 0.496011;
            case $z < -0.009:
                return 0.496410;
            case $z < -0.008:
                return 0.496808;
            case $z < -0.007:
                return 0.497207;
            case $z < -0.006:
                return 0.497606;
            case $z < -0.005:
                return 0.498005;
            case $z < -0.004:
                return 0.498404;
            case $z < -0.003:
                return 0.498803;
            case $z < -0.002:
                return 0.499202;
            case $z < -0.001:
                return 0.499601;
            case $z < 0.000:
                return 0.500000;
            case $z < 0.001:
                return 0.500399;
            case $z < 0.002:
                return 0.500798;
            case $z < 0.003:
                return 0.501197;
            case $z < 0.004:
                return 0.501596;
            case $z < 0.005:
                return 0.501995;
            case $z < 0.006:
                return 0.502394;
            case $z < 0.007:
                return 0.502793;
            case $z < 0.008:
                return 0.503192;
            case $z < 0.009:
                return 0.503590;
            case $z < 0.010:
                return 0.503989;
            case $z < 0.020:
                return 0.507978;
            case $z < 0.030:
                return 0.511966;
            case $z < 0.040:
                return 0.515953;
            case $z < 0.050:
                return 0.519939;
            case $z < 0.060:
                return 0.523922;
            case $z < 0.070:
                return 0.527903;
            case $z < 0.080:
                return 0.531881;
            case $z < 0.090:
                return 0.535856;
            case $z < 0.100:
                return 0.539828;
            case $z < 0.110:
                return 0.543795;
            case $z < 0.120:
                return 0.547758;
            case $z < 0.130:
                return 0.551717;
            case $z < 0.140:
                return 0.555670;
            case $z < 0.150:
                return 0.559618;
            case $z < 0.160:
                return 0.563559;
            case $z < 0.170:
                return 0.567495;
            case $z < 0.180:
                return 0.571424;
            case $z < 0.190:
                return 0.575345;
            case $z < 0.200:
                return 0.579260;
            case $z < 0.210:
                return 0.583166;
            case $z < 0.220:
                return 0.587064;
            case $z < 0.230:
                return 0.590954;
            case $z < 0.240:
                return 0.594835;
            case $z < 0.250:
                return 0.598706;
            case $z < 0.260:
                return 0.602568;
            case $z < 0.270:
                return 0.606420;
            case $z < 0.280:
                return 0.610261;
            case $z < 0.290:
                return 0.614092;
            case $z < 0.300:
                return 0.617911;
            case $z < 0.310:
                return 0.621720;
            case $z < 0.320:
                return 0.625516;
            case $z < 0.330:
                return 0.629300;
            case $z < 0.340:
                return 0.633072;
            case $z < 0.350:
                return 0.636831;
            case $z < 0.360:
                return 0.640576;
            case $z < 0.370:
                return 0.644309;
            case $z < 0.380:
                return 0.648027;
            case $z < 0.390:
                return 0.651732;
            case $z < 0.400:
                return 0.655422;
            case $z < 0.410:
                return 0.659097;
            case $z < 0.420:
                return 0.662757;
            case $z < 0.430:
                return 0.666402;
            case $z < 0.440:
                return 0.670031;
            case $z < 0.450:
                return 0.673645;
            case $z < 0.460:
                return 0.677242;
            case $z < 0.470:
                return 0.680822;
            case $z < 0.480:
                return 0.684386;
            case $z < 0.490:
                return 0.687933;
            case $z < 0.500:
                return 0.691462;
            case $z < 0.510:
                return 0.694974;
            case $z < 0.520:
                return 0.698468;
            case $z < 0.530:
                return 0.701944;
            case $z < 0.540:
                return 0.705401;
            case $z < 0.550:
                return 0.708840;
            case $z < 0.560:
                return 0.712260;
            case $z < 0.570:
                return 0.715661;
            case $z < 0.580:
                return 0.719043;
            case $z < 0.590:
                return 0.722405;
            case $z < 0.600:
                return 0.725747;
            case $z < 0.610:
                return 0.729069;
            case $z < 0.620:
                return 0.732371;
            case $z < 0.630:
                return 0.735653;
            case $z < 0.640:
                return 0.738914;
            case $z < 0.650:
                return 0.742154;
            case $z < 0.660:
                return 0.745373;
            case $z < 0.670:
                return 0.748571;
            case $z < 0.680:
                return 0.751748;
            case $z < 0.690:
                return 0.754903;
            case $z < 0.700:
                return 0.758036;
            case $z < 0.710:
                return 0.761148;
            case $z < 0.720:
                return 0.764238;
            case $z < 0.730:
                return 0.767305;
            case $z < 0.740:
                return 0.770350;
            case $z < 0.750:
                return 0.773373;
            case $z < 0.760:
                return 0.776373;
            case $z < 0.770:
                return 0.779350;
            case $z < 0.780:
                return 0.782305;
            case $z < 0.790:
                return 0.785236;
            case $z < 0.800:
                return 0.788145;
            case $z < 0.810:
                return 0.791030;
            case $z < 0.820:
                return 0.793892;
            case $z < 0.830:
                return 0.796731;
            case $z < 0.840:
                return 0.799546;
            case $z < 0.850:
                return 0.802337;
            case $z < 0.860:
                return 0.805105;
            case $z < 0.870:
                return 0.807850;
            case $z < 0.880:
                return 0.810570;
            case $z < 0.890:
                return 0.813267;
            case $z < 0.900:
                return 0.815940;
            case $z < 0.910:
                return 0.818589;
            case $z < 0.920:
                return 0.821214;
            case $z < 0.930:
                return 0.823814;
            case $z < 0.940:
                return 0.826391;
            case $z < 0.950:
                return 0.828944;
            case $z < 0.960:
                return 0.831472;
            case $z < 0.970:
                return 0.833977;
            case $z < 0.980:
                return 0.836457;
            case $z < 0.990:
                return 0.838913;
            case $z < 1.000:
                return 0.841345;
            case $z < 1.010:
                return 0.843752;
            case $z < 1.020:
                return 0.846136;
            case $z < 1.030:
                return 0.848495;
            case $z < 1.040:
                return 0.850830;
            case $z < 1.050:
                return 0.853141;
            case $z < 1.060:
                return 0.855428;
            case $z < 1.070:
                return 0.857690;
            case $z < 1.080:
                return 0.859929;
            case $z < 1.090:
                return 0.862143;
            case $z < 1.100:
                return 0.864334;
            case $z < 1.110:
                return 0.866500;
            case $z < 1.120:
                return 0.868643;
            case $z < 1.130:
                return 0.870762;
            case $z < 1.140:
                return 0.872857;
            case $z < 1.150:
                return 0.874928;
            case $z < 1.160:
                return 0.876976;
            case $z < 1.170:
                return 0.879000;
            case $z < 1.180:
                return 0.881000;
            case $z < 1.190:
                return 0.882977;
            case $z < 1.200:
                return 0.884930;
            case $z < 1.210:
                return 0.886861;
            case $z < 1.220:
                return 0.888768;
            case $z < 1.230:
                return 0.890651;
            case $z < 1.240:
                return 0.892512;
            case $z < 1.250:
                return 0.894350;
            case $z < 1.260:
                return 0.896165;
            case $z < 1.270:
                return 0.897958;
            case $z < 1.280:
                return 0.899727;
            case $z < 1.290:
                return 0.901475;
            case $z < 1.300:
                return 0.903200;
            case $z < 1.310:
                return 0.904902;
            case $z < 1.320:
                return 0.906582;
            case $z < 1.330:
                return 0.908241;
            case $z < 1.340:
                return 0.909877;
            case $z < 1.350:
                return 0.911492;
            case $z < 1.360:
                return 0.913085;
            case $z < 1.370:
                return 0.914657;
            case $z < 1.380:
                return 0.916207;
            case $z < 1.390:
                return 0.917736;
            case $z < 1.400:
                return 0.919243;
            case $z < 1.410:
                return 0.920730;
            case $z < 1.420:
                return 0.922196;
            case $z < 1.430:
                return 0.923641;
            case $z < 1.440:
                return 0.925066;
            case $z < 1.450:
                return 0.926471;
            case $z < 1.460:
                return 0.927855;
            case $z < 1.470:
                return 0.929219;
            case $z < 1.480:
                return 0.930563;
            case $z < 1.490:
                return 0.931888;
            case $z < 1.500:
                return 0.933193;
            case $z < 1.510:
                return 0.934478;
            case $z < 1.520:
                return 0.935745;
            case $z < 1.530:
                return 0.936992;
            case $z < 1.540:
                return 0.938220;
            case $z < 1.550:
                return 0.939429;
            case $z < 1.560:
                return 0.940620;
            case $z < 1.570:
                return 0.941792;
            case $z < 1.580:
                return 0.942947;
            case $z < 1.590:
                return 0.944083;
            case $z < 1.600:
                return 0.945201;
            case $z < 1.610:
                return 0.946301;
            case $z < 1.620:
                return 0.947384;
            case $z < 1.630:
                return 0.948449;
            case $z < 1.640:
                return 0.949497;
            case $z < 1.650:
                return 0.950529;
            case $z < 1.660:
                return 0.951543;
            case $z < 1.670:
                return 0.952540;
            case $z < 1.680:
                return 0.953521;
            case $z < 1.690:
                return 0.954486;
            case $z < 1.700:
                return 0.955435;
            case $z < 1.710:
                return 0.956367;
            case $z < 1.720:
                return 0.957284;
            case $z < 1.730:
                return 0.958185;
            case $z < 1.740:
                return 0.959070;
            case $z < 1.750:
                return 0.959941;
            case $z < 1.760:
                return 0.960796;
            case $z < 1.770:
                return 0.961636;
            case $z < 1.780:
                return 0.962462;
            case $z < 1.790:
                return 0.963273;
            case $z < 1.800:
                return 0.964070;
            case $z < 1.810:
                return 0.964852;
            case $z < 1.820:
                return 0.965620;
            case $z < 1.830:
                return 0.966375;
            case $z < 1.840:
                return 0.967116;
            case $z < 1.850:
                return 0.967843;
            case $z < 1.860:
                return 0.968557;
            case $z < 1.870:
                return 0.969258;
            case $z < 1.880:
                return 0.969946;
            case $z < 1.890:
                return 0.970621;
            case $z < 1.900:
                return 0.971283;
            case $z < 1.910:
                return 0.971933;
            case $z < 1.920:
                return 0.972571;
            case $z < 1.930:
                return 0.973197;
            case $z < 1.940:
                return 0.973810;
            case $z < 1.950:
                return 0.974412;
            case $z < 1.960:
                return 0.975002;
            case $z < 1.970:
                return 0.975581;
            case $z < 1.980:
                return 0.976148;
            case $z < 1.990:
                return 0.976705;
            case $z < 2.000:
                return 0.977250;
            case $z < 2.010:
                return 0.977784;
            case $z < 2.020:
                return 0.978308;
            case $z < 2.030:
                return 0.978822;
            case $z < 2.040:
                return 0.979325;
            case $z < 2.050:
                return 0.979818;
            case $z < 2.060:
                return 0.980301;
            case $z < 2.070:
                return 0.980774;
            case $z < 2.080:
                return 0.981237;
            case $z < 2.090:
                return 0.981691;
            case $z < 2.100:
                return 0.982136;
            case $z < 2.110:
                return 0.982571;
            case $z < 2.120:
                return 0.982997;
            case $z < 2.130:
                return 0.983414;
            case $z < 2.140:
                return 0.983823;
            case $z < 2.150:
                return 0.984222;
            case $z < 2.160:
                return 0.984614;
            case $z < 2.170:
                return 0.984997;
            case $z < 2.180:
                return 0.985371;
            case $z < 2.190:
                return 0.985738;
            case $z < 2.200:
                return 0.986097;
            case $z < 2.210:
                return 0.986447;
            case $z < 2.220:
                return 0.986791;
            case $z < 2.230:
                return 0.987126;
            case $z < 2.240:
                return 0.987455;
            case $z < 2.250:
                return 0.987776;
            case $z < 2.260:
                return 0.988089;
            case $z < 2.270:
                return 0.988396;
            case $z < 2.280:
                return 0.988696;
            case $z < 2.290:
                return 0.988989;
            case $z < 2.300:
                return 0.989276;
            case $z < 2.310:
                return 0.989556;
            case $z < 2.320:
                return 0.989830;
            case $z < 2.330:
                return 0.990097;
            case $z < 2.340:
                return 0.990358;
            case $z < 2.350:
                return 0.990613;
            case $z < 2.360:
                return 0.990863;
            case $z < 2.370:
                return 0.991106;
            case $z < 2.380:
                return 0.991344;
            case $z < 2.390:
                return 0.991576;
            case $z < 2.400:
                return 0.991802;
            case $z < 2.410:
                return 0.992024;
            case $z < 2.420:
                return 0.992240;
            case $z < 2.430:
                return 0.992451;
            case $z < 2.440:
                return 0.992656;
            case $z < 2.450:
                return 0.992857;
            case $z < 2.460:
                return 0.993053;
            case $z < 2.470:
                return 0.993244;
            case $z < 2.480:
                return 0.993431;
            case $z < 2.490:
                return 0.993613;
            case $z < 2.500:
                return 0.993790;
            case $z < 2.510:
                return 0.993963;
            case $z < 2.520:
                return 0.994132;
            case $z < 2.530:
                return 0.994297;
            case $z < 2.540:
                return 0.994457;
            case $z < 2.550:
                return 0.994614;
            case $z < 2.560:
                return 0.994766;
            case $z < 2.570:
                return 0.994915;
            case $z < 2.580:
                return 0.995060;
            case $z < 2.590:
                return 0.995201;
            case $z < 2.600:
                return 0.995339;
            case $z < 2.610:
                return 0.995473;
            case $z < 2.620:
                return 0.995604;
            case $z < 2.630:
                return 0.995731;
            case $z < 2.640:
                return 0.995855;
            case $z < 2.650:
                return 0.995975;
            case $z < 2.660:
                return 0.996093;
            case $z < 2.670:
                return 0.996207;
            case $z < 2.680:
                return 0.996319;
            case $z < 2.690:
                return 0.996427;
            case $z < 2.700:
                return 0.996533;
            case $z < 2.710:
                return 0.996636;
            case $z < 2.720:
                return 0.996736;
            case $z < 2.730:
                return 0.996833;
            case $z < 2.740:
                return 0.996928;
            case $z < 2.750:
                return 0.997020;
            case $z < 2.760:
                return 0.997110;
            case $z < 2.770:
                return 0.997197;
            case $z < 2.780:
                return 0.997282;
            case $z < 2.790:
                return 0.997365;
            case $z < 2.800:
                return 0.997445;
            case $z < 2.810:
                return 0.997523;
            case $z < 2.820:
                return 0.997599;
            case $z < 2.830:
                return 0.997673;
            case $z < 2.840:
                return 0.997744;
            case $z < 2.850:
                return 0.997814;
            case $z < 2.860:
                return 0.997882;
            case $z < 2.870:
                return 0.997948;
            case $z < 2.880:
                return 0.998012;
            case $z < 2.890:
                return 0.998074;
            case $z < 2.900:
                return 0.998134;
            case $z < 2.910:
                return 0.998193;
            case $z < 2.920:
                return 0.998250;
            case $z < 2.930:
                return 0.998305;
            case $z < 2.940:
                return 0.998359;
            case $z < 2.950:
                return 0.998411;
            case $z < 2.960:
                return 0.998462;
            case $z < 2.970:
                return 0.998511;
            case $z < 2.980:
                return 0.998559;
            case $z < 2.990:
                return 0.998605;
            case $z < 3.000:
                return 0.998650;
            case $z < 3.010:
                return 0.998694;
            case $z < 3.020:
                return 0.998736;
            case $z < 3.030:
                return 0.998777;
            case $z < 3.040:
                return 0.998817;
            case $z < 3.050:
                return 0.998856;
            case $z < 3.060:
                return 0.998893;
            case $z < 3.070:
                return 0.998930;
            case $z < 3.080:
                return 0.998965;
            case $z < 3.090:
                return 0.998999;
            case $z < 3.100:
                return 0.999032;
            case $z < 3.110:
                return 0.999065;
            case $z < 3.120:
                return 0.999096;
            case $z < 3.130:
                return 0.999126;
            case $z < 3.140:
                return 0.999155;
            case $z < 3.150:
                return 0.999184;
            case $z < 3.160:
                return 0.999211;
            case $z < 3.170:
                return 0.999238;
            case $z < 3.180:
                return 0.999264;
            case $z < 3.190:
                return 0.999289;
            case $z < 3.200:
                return 0.999313;
            case $z < 3.210:
                return 0.999336;
            case $z < 3.220:
                return 0.999359;
            case $z < 3.230:
                return 0.999381;
            case $z < 3.240:
                return 0.999402;
            case $z < 3.250:
                return 0.999423;
            case $z < 3.260:
                return 0.999443;
            case $z < 3.270:
                return 0.999462;
            case $z < 3.280:
                return 0.999481;
            case $z < 3.290:
                return 0.999499;
            case $z < 3.300:
                return 0.999517;
            case $z < 3.310:
                return 0.999534;
            case $z < 3.320:
                return 0.999550;
            case $z < 3.330:
                return 0.999566;
            case $z < 3.340:
                return 0.999581;
            case $z < 3.350:
                return 0.999596;
            case $z < 3.360:
                return 0.999610;
            case $z < 3.370:
                return 0.999624;
            case $z < 3.380:
                return 0.999638;
            case $z < 3.390:
                return 0.999651;
            case $z < 3.400:
                return 0.999663;
            case $z < 3.410:
                return 0.999675;
            case $z < 3.420:
                return 0.999687;
            case $z < 3.430:
                return 0.999698;
            case $z < 3.440:
                return 0.999709;
            case $z < 3.450:
                return 0.999720;
            case $z < 3.460:
                return 0.999730;
            case $z < 3.470:
                return 0.999740;
            case $z < 3.480:
                return 0.999749;
            case $z < 3.490:
                return 0.999758;
            case $z < 3.500:
                return 0.999767;
            case $z < 3.510:
                return 0.999776;
            case $z < 3.520:
                return 0.999784;
            case $z < 3.530:
                return 0.999792;
            case $z < 3.540:
                return 0.999800;
            case $z < 3.550:
                return 0.999807;
            case $z < 3.560:
                return 0.999815;
            case $z < 3.570:
                return 0.999822;
            case $z < 3.580:
                return 0.999828;
            case $z < 3.590:
                return 0.999835;
            case $z < 3.600:
                return 0.999841;
            case $z < 3.610:
                return 0.999847;
            case $z < 3.620:
                return 0.999853;
            case $z < 3.630:
                return 0.999858;
            case $z < 3.640:
                return 0.999864;
            case $z < 3.650:
                return 0.999869;
            case $z < 3.660:
                return 0.999874;
            case $z < 3.670:
                return 0.999879;
            case $z < 3.680:
                return 0.999883;
            case $z < 3.690:
                return 0.999888;
            case $z < 3.700:
                return 0.999892;
            case $z < 3.710:
                return 0.999896;
            case $z < 3.720:
                return 0.999900;
            case $z < 3.730:
                return 0.999904;
            case $z < 3.740:
                return 0.999908;
            case $z < 3.750:
                return 0.999912;
            case $z < 3.760:
                return 0.999915;
            case $z < 3.770:
                return 0.999918;
            case $z < 3.780:
                return 0.999922;
            case $z < 3.790:
                return 0.999925;
            case $z < 3.800:
                return 0.999928;
            case $z < 3.810:
                return 0.999931;
            case $z < 3.820:
                return 0.999933;
            case $z < 3.830:
                return 0.999936;
            case $z < 3.840:
                return 0.999938;
            case $z < 3.850:
                return 0.999941;
            case $z < 3.860:
                return 0.999943;
            case $z < 3.870:
                return 0.999946;
            case $z < 3.880:
                return 0.999948;
            case $z < 3.890:
                return 0.999950;
            default:
                return 1.0;
        }

    }
}
