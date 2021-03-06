<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;
use Carbon\Carbon;

class MarketDataController
{
    public function intraDayStats($asx_code)
    {
        date_default_timezone_set('UTC');
        $insert_count = 0;
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=" . $asx_code . ".AX&interval=1min&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        $output['asx_code'] = strtolower($resp->{'Meta Data'}->{'2. Symbol'});
        $output['last_refreshed'] = $resp->{'Meta Data'}->{'3. Last Refreshed'};

        foreach ($resp->{'Time Series (1min)'} as $key => $record) {
            $insert_count += DB::table('stocks_minutely')->insert([
                'created_at_utc' => Carbon::now(),
                'last_refreshed' => $output['last_refreshed'],
                'asx_code' => $output['asx_code'],
                'date' => $key,
                'open' => $record->{'1. open'},
                'high' => $record->{'2. high'},
                'low' => $record->{'3. low'},
                'close' => $record->{'4. close'},
                'volume' => $record->{'5. volume'}
            ]);
        }
        return $insert_count . ' records added';
    }

    public function dailyStats($asx_code)
    {
        date_default_timezone_set('UTC');
        $insert_count = 0;
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . $asx_code . ".AX&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        $output['asx_code'] = strtolower($resp->{'Meta Data'}->{'2. Symbol'});
        $output['last_refreshed'] = $resp->{'Meta Data'}->{'3. Last Refreshed'};

        foreach ($resp->{'Time Series (Daily)'} as $key => $record) {
            $insert_count += DB::table('stocks_daily')->insert([
                            'created_at_utc' => Carbon::now(),
                            'last_refreshed' => $output['last_refreshed'],
                            'asx_code' => $output['asx_code'],
                            'date' => $key,
                            'open' => $record->{'1. open'},
                            'high' => $record->{'2. high'},
                            'low' => $record->{'3. low'},
                            'close' => $record->{'4. close'},
                            'volume' => $record->{'5. volume'}
            ]);
        }
        return $insert_count . ' records added';
    }

    public function weeklyStats($asx_code)
    {
        date_default_timezone_set('UTC');
        $insert_count = 0;
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_WEEKLY&symbol=" . $asx_code . ".AX&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        $output['asx_code'] = strtolower($resp->{'Meta Data'}->{'2. Symbol'});
        $output['last_refreshed'] = $resp->{'Meta Data'}->{'3. Last Refreshed'};

        foreach ($resp->{'Weekly Time Series'} as $key => $record) {
            $insert_count += DB::table('stocks_weekly')->insert([
                'created_at_utc' => Carbon::now(),
                'last_refreshed' => $output['last_refreshed'],
                'asx_code' => $output['asx_code'],
                'date' => $key,
                'open' => $record->{'1. open'},
                'high' => $record->{'2. high'},
                'low' => $record->{'3. low'},
                'close' => $record->{'4. close'},
                'volume' => $record->{'5. volume'}
            ]);
        }
        return $insert_count . ' records added';
    }

    public function monthlyStats($asx_code)
    {
        date_default_timezone_set('UTC');
        $insert_count = 0;
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY&symbol=" . $asx_code . ".AX&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        if (isset($resp->{'Meta Data'})) {
            foreach ($resp->{'Monthly Time Series'} as $key => $record) {
                $insert_count += DB::table('stocks_monthly')->insert([
                    'created_at_utc' => Carbon::now(),
                    'last_refreshed' => $resp->{'Meta Data'}->{'3. Last Refreshed'},
                    'asx_code' => strtolower($resp->{'Meta Data'}->{'2. Symbol'}),
                    'date' => $key,
                    'open' => $record->{'1. open'},
                    'high' => $record->{'2. high'},
                    'low' => $record->{'3. low'},
                    'close' => $record->{'4. close'},
                    'volume' => $record->{'5. volume'}
                ]);
                $success = DB::table('asx_company_details')
                        ->where('company_code', $asx_code)
                        ->update(['status' => 'active']);
            }
            return $success . ', ' . $insert_count . ' records added';
        } else {
            $failed = DB::table('asx_company_details')
                        ->where('company_code', $asx_code)
                        ->update(['status' => 'failed']);
            return $failed;
        }
    }

    public static function curlStocksStats($url)
    {
        // Get cURL resource
        $curl = curl_init();
        // Set some options
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));
        // Send the request & save response to $resp
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, getcwd() . "/DSTRootCAX3.crt");

        $resp = curl_exec($curl);
        if (!curl_exec($curl)) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        }

        // Close request to clear up some resources
        curl_close($curl);

        // Return the results
        return $resp;
    }

    public function getCompanyDetails($asx)
    {
        $asx = strtoupper($asx);
        $company = DB::table('asx_company_details')->where('company_code', '=', $asx)
            ->limit(10)
            ->get();
        return $company;
    }

    public function getCompanyName($string)
    {
        $string = '%'. $string . '%';
        $company = DB::table('asx_company_details')->where('company_name', 'like', $string)
            ->limit(10)
            ->get();
        return $company;
    }

    public function getmonthly($asx_code){
        $output = array();

        date_default_timezone_set('UTC');
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY&symbol=" . $asx_code . ".AX&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        if (isset($resp->{'Meta Data'})) {
            foreach ($resp->{'Monthly Time Series'} as $key => $record) {
                $data[] = array(
                    'unix_date' => Carbon::createFromFormat('Y-m-d', $key)->timestamp * 1000,
                    'open' => (float)$record->{'1. open'},
                    'high' => (float)$record->{'2. high'},
                    'low' => (float)$record->{'3. low'},
                    'close' => (float)$record->{'4. close'},
                    'volume' => (int)$record->{'5. volume'}
                );
            }
        }

        foreach (array_reverse($data) as $value) {
            $current_array = array();
            foreach ($value as $value2) {
                array_push($current_array,$value2);
            }
            array_push($output, $current_array);
        }

        return $output;
    }

    public function getrealtime($asx_code){
        $output = array();

        date_default_timezone_set('UTC');
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=" . $asx_code . ".AX&interval=1min&outputsize=full&apikey=PEQIWLTYB0GPLMB8";
        $resp = $this->curlStocksStats($url);
        $resp = json_decode($resp);

        if (isset($resp->{'Meta Data'})) {
            foreach ($resp->{'Time Series (1min)'} as $key => $record) {
                $data[] = array(
                    'unix_date' => Carbon::createFromFormat('Y-m-d H:i:s', $key)->timestamp * 1000,
                    'open' => (float)$record->{'1. open'},
                    'high' => (float)$record->{'2. high'},
                    'low' => (float)$record->{'3. low'},
                    'close' => (float)$record->{'4. close'},
                    'volume' => (int)$record->{'5. volume'}
                );
            }
        }

        foreach (array_reverse($data) as $value) {
            $current_array = array();
            foreach ($value as $value2) {
                array_push($current_array,$value2);
            }
            array_push($output, $current_array);
        }

        return $output;
    }

    public function populateMonthlyStocks($limit){
        $output = array();
        $current = 0;

        $allCompanyDetails = DB::select('SELECT * FROM asx_company_details WHERE status = "pending" LIMIT ' . $limit);
        foreach ($allCompanyDetails as $key => $value) {
            $asx_code = strtolower($value->company_code) . '.ax';
            $existingAddition = DB::select('SELECT asx_code FROM stocks_monthly WHERE DATE(last_refreshed) >= DATE(NOW() - INTERVAL 1 MONTH) AND asx_code = "'. $asx_code .'" GROUP BY asx_code');

            if (!isset($existingAddition[0])) {
                $output[$asx_code] = $this->monthlyStats($value->company_code);
            } else {
                $success = DB::table('asx_company_details')
                        ->where('company_code', $value->company_code)
                        ->update(['status' => 'active']);
            }
        }

        return $output;
    }
}