<link rel="stylesheet" href="css/style.css">
<nav class="navbar">

    <div class="logo">
        <h2>The Row3 Concert</h2>
    </div>

    <div class="nav-links">

        <a href="Dashboard.php">Home</a>
        <a href="about.php">About</a>

        <?php
// IF USER IS LOGGED IN
        if(isset($_SESSION["userID"])){
            if($_SESSION["role"] == "Admin"){ 
        ?>

                <a href="admin/dashboard.php">Dashboard</a>
                <a href="admin/reservations.php">Reservations</a>
                <a href="admin/tickets.php">Tickets</a>
                <a href="logout.php">Logout</a>

        <?php
            }
            else{
        ?>
                <a href="reserve.php">Reserve Ticket</a>
                <a href="myticket.php">My Ticket</a>
                <a href="profile.php">Profile</a>
                

        <?php
            }
        }
        
// IF USER IS NOT LOGGED IN
        else{
        ?>
            <a href="reserve.php">Reserve Ticket</a>
            <a href="myticket.php">My Ticket</a>
            <a href="profile.php">Profile</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>

        <?php
        }
        ?>

    </div>

</nav>