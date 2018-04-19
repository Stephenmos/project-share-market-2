@extends('layouts/main-template')

@section('content')

    <!-- PAGE SPECIFIC CONTENT GOES HERE -->

    <div class = "sysoBox sysoBoxFlex" id="commBox">
        <div class = "sysoContent sysoContent50">
            
            <div class="friends">
                <br/>
                <h1>Top 10</h1>
                <table class="friendList">
                <tr id = "tableHeader">
                    <th>Name</th>
                    <th>Total Worth</th>
                    <th>Friend</th>
                </tr>

                <?php 
                    //Top 10 Leaderboard
                    $users = DB::table('users')->get();
                    //SELECT * FROM articles ORDER BY rating DESC LIMIT 10
                    $data = $users->sortByDesc('account_balance')->take('10');
                    $name=null;
                    $balance=0.00;
                    foreach ($data as $line) {
                    $name=($line->name);
                    $balance=($line->account_balance);
                    echo "<tr>";
                    echo "<td>".$name."</td>";
                    echo "<td>".$balance."</td>";
                    echo "<td><button name='friend'>Friend</button></td>";
                    echo "</tr>";
                    }
                ?>

                </table>
                <br/>
            </div>
            
            <div class="friends">
                <h1>Search for User</h1>
                <form> 
                    <input type='text' name='user_name' placeholder='Enter User Name'>
                    <input type='submit' value='Search'>
                </form>
                <table class="friendList">
                <tr id = "tableHeader">
                    <th>Name</th>
                    <th>Total Worth</th>
                    <th>Friend</th>
                </tr>
                
                <?php
                    //Search User
                    $username = Request::get('user_name');
                    if ($username == null){
                        echo "<tr></tr>";
                    }
                    else {
                        $userdata = DB::table('users')->where('name', 'like', '%'.$username.'%')->get();
                        $uname=null;
                        $ubalance=0.00;
                            foreach ($userdata as $uline) {
                            $uname=($uline->name);
                            $ubalance=($uline->account_balance);
                            echo "<tr>";
                            echo "<td>".$uname."</td>";
                            echo "<td>".$ubalance."</td>";
                            echo "<td><button name='friend'>Friend</button></td>";
                            echo "</tr>";
                            }
                    }
                ?>

                </table>
            </div>

        </div>

        <div class = "sysoContent sysoContent50">
            
            <div class="friends">
                <br/>
                <h1>Friends</h1>
                <table class="friendList">
                <tr id = "tableHeader">
                    <th>Name</th>
                    <th>Total Worth</th>
                    <th>Unfriend</th>
                </tr>
                
                <tr>
                    <td></td>
                    <td></td>
                    <td><button name='friend'>Unfriend</button></td>
                </tr>

                </table>
            </div>
        
        </div>

    </div>

    <!-- END OF CONTENT -->

@endsection