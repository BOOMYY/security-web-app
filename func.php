<?php include( 'alertconfig.php' );
?>
<?php

// MULTI-USER LOGIN
try{
$con = mysqli_connect( 'localhost', 'root', '', 'hmsdbs' );


session_start();
if ( isset( $_POST['loginFormSubmit'] )) {
    
    $email = mysqli_real_escape_string($con, $_POST['username']);
    $password2 = mysqli_real_escape_string($con, $_POST['password']);
    
    $query = "SELECT * FROM admindb WHERE email='$_POST[username]'";
    $result = mysqli_query( $con, $query );

    
    $querys = "SELECT * FROM patienttb WHERE email='$_POST[username]'";
    $results = mysqli_query( $con, $querys );

    $super_ad = "SELECT * FROM super_admin_tb WHERE username='$email' AND password = '$password2'";
    $res = mysqli_query( $con, $super_ad );
 
    if (mysqli_num_rows($result) > 0) {
        while($rows = mysqli_fetch_assoc($result)){
            $hashed = $rows['password'];
            if(password_verify($password2, $hashed)){
            $status = '.admin';
            $_SESSION['id'] = 'admin';
        } else {
            $status = '.error';
        }
    }
    }else if(mysqli_num_rows( $res ) > 0){
        $status = '.admin';
        $_SESSION['id'] = 'super_admin';
    }else if ( mysqli_num_rows( $results ) > 0 ) {
        while($row = mysqli_fetch_assoc($results)){
            $hash = $row['password'];
            if(password_verify($password2, $hash)){
                $_SESSION['id'] = $id;
                $id = $row['fname'];
                $_SESSION['fname'] = $id;
                $ids = $row['patient_id'];
                $_SESSION['id'] = $ids;
                $lname = $row['lname'];
                $_SESSION['lname'] = $lname;
                $gender = $row['gender'];
                $_SESSION['gender'] = $gender;
                $email = $row['email'];
                $_SESSION['email'] = $email;
                $address = $row['adds'];
                $_SESSION['adds'] = $address;
                $regiDate = $row['regiDate'];
                $_SESSION['regiDate'] = $regiDate;
                $_SESSION['auth']='true';
            
        }else{
            $status = '.error'; 
        }
    }
    } else {
        $status = '.error';
    }

    echo $status;
}
}catch(Exception $e){
    echo 'Error Login', $e->getMessage();
}



// BOOKING/ADDING AN APPOINTMENT
try{
if ( isset( $_POST['pat_submit'] ) ) {

    $fname = $_SESSION['fname'];
    $lname = $_SESSION['lname'];
    $email = $_SESSION['email'];
    $mobile = $_POST['mobile'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $services = $_POST['services'];
    $id = $_SESSION['id'];
    $stats = $_POST['pend'];
    $payment = $_POST['payment'];

    $query = "INSERT INTO appointment(Fname, Lname,email, Mobile, Appointment_Date, Appointment_Time, Appointment_Service,patient_fk, stats, Payment)
     VALUE('$fname','$lname','$email','$mobile','$date','$time','$services','$id','$stats', '$payment')";
    $result = mysqli_query( $con, $query );

    if ( $result ) {
        echo "<script>alert('Appointment Added!')</script>";
        echo "<script>window.open('patient_BookAppointment.php', '_self')</script>";
    } else {
        echo $id;
        echo "<script>alert('Error Adding Appointment!')</script>";
        echo "<script>window.open('patient_BookAppointment.php', '_self')</script>";
    }
}
}catch(Exception $e){
    echo 'ERROR Addding appointment: ', $e->getMessage();
}

//PATIENT REGISTRATION
try {
    $pattern = '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/';
    if ( isset( $_POST['pat_register'] ) ) {
        $fname = ucwords( $_POST['fname'] );
        $lname = ucwords( $_POST['lname'] );
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $adds = $_POST['address'];
        $password = $_POST['password'];

        // ? HASHED PASSWORD
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query_email = "SELECT * FROM patienttb WHERE email='$email'";
        $res = mysqli_query( $con, $query_email );
        if ( mysqli_num_rows( $res ) > 0 ) {
            echo "<script>alert('Email Already Exist!')</script>";
            echo "<script>window.open('index.php', '_self')</script>";

        } else {

            if ( preg_match( $pattern, $password ) ) {
                $query = "INSERT INTO patienttb(fname, lname,gender,email,adds, password) VALUE('$fname','$lname','$gender','$email','$adds','$hashed_password')";
                $result = mysqli_query( $con, $query );
                if ( $result ) {
                    echo "<script>alert('Success Registering!')</script>";
                    echo "<script>window.open('index.php', '_self')</script>";
                } else {
                    echo "<script>alert('Error Registering!')</script>";
                    echo "<script>window.open('index.php', '_self')</script>";
                }
            } else {
                echo "<script>alert('Error Registering!')</script>";
            }
        }
    }
} catch( Exception $e ) {
    echo 'Error Patient Register',$e->getMessage();
}

//PATIENT ADD MEDICAL HISTORY
try {
    if ( isset( $_POST['addsubmit'] ) ) {
        $addid = $_POST['addid'];
        $bpupper = $_POST['upper'];
        $bplower = $_POST['lower'];
        $weight = $_POST['weight'];
        $temp = $_POST['temp'];
        $mh = $_POST['med_history'];

        $query = "INSERT INTO medicalhistorytb(patient_id, Blood_Pressure, Weight,Temperature, Medical_History)VALUE('$addid','$bpupper/$bplower','$weight','$temp','$mh')";
        $result = mysqli_query( $con, $query );

        if ( $result ) {
            $_SESSION['status'] = 'Added Successfully!';
            $_SESSION['status_code'] = 'success';
            echo "<script>window.open('patientList.php', '_self')</script>";
        } else {
            $_SESSION['status'] = 'Something went wrong!';
            $_SESSION['status_code'] = 'error';
            echo "<script>window.open('patientList.php', '_self')</script>";
        }
    }
} catch( Exception $e ) {
    echo 'ERROR PATIENT HISTORY',$e->getMessage();
}

//POPULATE THE DATA FROM DATABASE IN appointment TO TABLE IN APPOINTMENT HISTORY

function getPatientAppointment() {
    try{
    global $con;
    $stats = 'PENDING';
    $query = "SELECT * FROM appointment WHERE stats='$stats'";
    $result = mysqli_query( $con, $query );

    while( $row = mysqli_fetch_array( $result ) ) {
        $ids = $row['Appointment_Id'];
        $fname = $row['Fname'];
        $lname = $row['Lname'];
        $email = $row['Email'];
        $mobile = $row['Mobile'];
        $date = $row['Appointment_Date'];
        $time = $row['Appointment_Time'];
        $services = $row['Appointment_Service'];
        $patientfk = $row['patient_fk'];
        $status = $row['stats'];
        echo "<tr> 
            <td>$ids</td>
            <td>$fname</td>
            <td>$lname</td>
            <td>$mobile</td>
            <td>$date</td>
            <td>$time</td>
            <td>$services</td> 
            
            <td>
            <button type='button' class='approvebtn btn btn-success'>Approve</button>
            <button type='button' class='declinebtn btn btn-danger'>Decline</button>
            </td>
            </tr>";
    }
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}
}


//PATIENT APPOINTMENT HISTORY, dito sana mafefetch lang yung info nung user na naka login
// try{
//     function getPatientAppointmentLogs() {
//         global $con;
//         $ids = $_SESSION['id'];
//         $query = "SELECT * FROM appointment WHERE patient_fk='$ids'";
//         $result = mysqli_query( $con, $query );
    
//         while( $row = mysqli_fetch_array( $result ) ) {
//             $ids = $row['Appointment_Id'];
//             $fname = $row['Fname'];
//             $lname = $row['Lname'];
//             $date = $row['Appointment_Date'];
//             $time = $row['Appointment_Time'];
//             $services = $row['Appointment_Service'];
//             $patientfk = $row['patient_fk'];
//             $status = $row['stats'];
//             echo "<tr> 
//                 <td>$ids</td>
//                 <td>$fname</td>
//                 <td>$lname</td>
//                 <td>$date</td>
//                 <td>$time</td>
//                 <td>$services</td> 
//                 <td>$status</td>
//                 </tr>";
//         }
//     }
// }catch(Exception $e){
//     echo 'error contact admin';
// }

function getPatientAppointmentHistory() {
    try{
    global $con;
    $query = 'SELECT * FROM appointment';
    $result = mysqli_query( $con, $query );

    while( $row = mysqli_fetch_array( $result ) ) {
        $ids = $row['Appointment_Id'];
        $fname = $row['Fname'];
        $lname = $row['Lname'];
        $email = $row['Email'];
        $mobile = $row['Mobile'];
        $date = $row['Appointment_Date'];
        $time = $row['Appointment_Time'];
        $services = $row['Appointment_Service'];
        $patientfk = $row['patient_fk'];
        $status = $row['stats'];
        echo "<tr> 
            <td>$ids</td>
            <td>$fname</td>
            <td>$lname</td>
            <td>$email</td>
            <td>$mobile</td>
            <td>$date</td>
            <td>$time</td>
            <td>$services</td> 
            <td>$status</td> 
            </tr>";
    }
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}
}

//PATIENT APPOINTMENT HISTORY, dito sana mafefetch lang yung info nung user na naka login

function getPatientAppointmentLogs() {
    try{
    global $con;
    $ids = $_SESSION['id'];
    $query = "SELECT * FROM appointment WHERE patient_fk='$ids'";
    $result = mysqli_query( $con, $query );

    while( $row = mysqli_fetch_array( $result ) ) {
        $ids = $row['Appointment_Id'];
        $fname = $row['Fname'];
        $lname = $row['Lname'];
        $date = $row['Appointment_Date'];
        $time = $row['Appointment_Time'];
        $services = $row['Appointment_Service'];
        $patientfk = $row['patient_fk'];
        $status = $row['stats'];
        echo "<tr> 
            <td>$ids</td>
            <td>$fname</td>
            <td>$lname</td>
            <td>$date</td>
            <td>$time</td>
            <td>$services</td> 
            <td>$status</td>
            </tr>";
    }
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}
}

//POPULATE THE DATA FROM DATABASE IN patienttb TO TABLE IN ADMIN PATIENT LIST

function getPatientDetails() {
    try{
    global $con;

    $query = 'SELECT * FROM patienttb';
    $result = mysqli_query( $con, $query );

    while( $row = mysqli_fetch_array( $result ) ) {
        $id = $row['patient_id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $gender = $row['gender'];
        $email = $row['email'];
        $address = $row['adds'];
        $regiDate = $row['regiDate'];
        echo "
        <tr>
            <td>$id</td>
            <td>$fname</td>
            <td>$lname</td>
            <td>$gender</td>
            <td>$email</td>
            <td>$address</td>
            <td>
            <form action='admin_ViewMedicalHistory.php' method='post'>
                <input type='text' name='id' value='$id' style='display:none'>
                <input type='text' name='fname' value='$fname' style='display:none'>
                <input type='text' name='lname' value='$lname' style='display:none'>
                <input type='text' name='email' value='$gender' style='display:none'>
                <input type='text' name='gender' value='$email' style='display:none'>
                <input type='text' name='address' value='$address' style='display:none'>
                <input type='text' name='regiDate' value='$regiDate' style='display:none'>
                <button type='submit' class='viewbtn btn btn-success' name= 'view_btn'>View</button>
            
                <button type='button' class='editbtn btn btn-info' name= 'edit_btn'>Edit</button> 
                <button type='button' class='deletebtn btn btn-danger' name= 'delete_btn'>Delete</button>
            </form>
            </td>
        </tr>";
    }
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}
}

function getPatientMedicalHistory( $x ) {
    try{
    global $con;
    $id = $x;

    $query = "SELECT * FROM medicalhistorytb WHERE patient_id = '$id'";
    $result = mysqli_query( $con, $query );

    while( $row = mysqli_fetch_array( $result ) ) {
        $id = $row['mh_id'];
        $bp = $row['Blood_Pressure'];
        $weight = $row['Weight'];
        $temp = $row['Temperature'];
        $mh = $row['Medical_History'];

        echo "<tr>
            <td>$id</td>
            <td>$bp</td>
            <td>$weight</td>
            <td>$temp</td>
            <td>$mh</td>
            </tr>
            ";
    }
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}
}
// UPDATE PAYMENT OF PATIENT
try {
    if ( isset( $_POST['update_data'] ) ) {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $query = "UPDATE paymenttable SET Payment_Status='$status' WHERE Payment_Id='$id'";
        $result =  mysqli_query( $con, $query );

        if ( $result ) {
            
            
            echo "<script>alert('Payment Status Updated!')</script>";
            echo "<script>window.open('admin_Payment.php', '_self')</script>";
        } else {
            header( 'Location:updated.php' );
        }
    }
} catch( Exception $e ) {
    echo $e->getMessage();
}

//UPDATE THE PATIENT LIST TABLE
try {
    if ( isset( $_POST['edit'] ) ) {
        $id = $_POST['id'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $add = $_POST['address'];

        $query = "UPDATE patienttb SET fname= '$fname', lname='$lname',gender='$gender',email= '$email',adds='$add' WHERE patient_id = '$id' ";
        $query_run =  mysqli_query( $con, $query );
        if ( $query_run ) {
            // echo "<script>alert('Payment Status Updated!')</script>";
            $_SESSION['status'] = 'Updated Successfully!';
            $_SESSION['status_code'] = 'success';
            header( 'location:patientList.php' );
        } else {
            $_SESSION['status'] = 'Something went wrong!';
            $_SESSION['status_code'] = 'error';
            header( 'Location:updated.php' );
        }
    }
} catch( Exception $e ) {
    echo $e->getMessage();
}

//DELETE FUNCTION IN POPULATING THE DATA IN THE DATABASE IN patienttb TO ADMIN PATIENT LIST
try {
    if ( isset( $_POST['delete'] ) ) {
        $deleteId = $_POST['id'];

        $query = "DELETE FROM patienttb WHERE patient_id = '$deleteId'";
        $query_run =  mysqli_query( $con, $query );
        if ( $query_run ) {
            // echo "<script>alert('Payment Status Updated!')</script>";
            $_SESSION['status'] = 'Delete Successfully!';
            $_SESSION['status_code'] = 'success';
            header( 'location:patientList.php' );
        } else {
            $_SESSION['status'] = 'Something went wrong!';
            $_SESSION['status_code'] = 'error';
            header( 'Location:updated.php' );
        }

    }
} catch( Exception $e ) {
    echo $e->getMessage();
}

// show the fname and lname from book appointment
// try{
//     $fname = 'SELECT fname FROM patienttb';
//     $lname = 'SELECT lname FROM patienttb';
//     $resultfname = mysqli_query( $con, $fname);
//     $resultlname = mysqli_query( $con, $lname);
//     $values = mysqli_fetch_array($resultfname);
    
// }catch(Exception $e){
//     echo 'ERROR',$e->getMessage();
// }

// COUNT THE TOTAL APPOINTMENT
try{
  
$query_count = 'SELECT (Appointment_Id) FROM appointment';
$result = mysqli_query( $con, $query_count );
$values = mysqli_num_rows( $result );
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}

//TOTAL PATIENT
try{
$query_count = 'SELECT (patient_id) AS TOTAL FROM patienttb';
$result = mysqli_query( $con, $query_count );
$patient_values = mysqli_num_rows( $result );
}catch(Exception $e){
    echo 'ERROR',$e->getMessage();
}

//approve button appointmentHistory.php
try {
    if ( isset( $_POST['approve'] ) ) {
        $id = $_POST['id'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $service = $_POST['service'];
        $status = 'NOT PAID';

        echo "<script>alert('$id $fname $lname $service $status')</script>";
        
        $query = "INSERT INTO paymenttable(Appointment_Id, Fname, Lname,Date,Time,Appointment_Service, Payment_Status)VALUE('$id','$fname','$lname','$date','$time','$service','$status')";
        $result = mysqli_query( $con, $query );

        if ( $result ) {
            $stat = 'APPROVED';
            $querys = "UPDATE appointment SET stats='$stat' WHERE Appointment_Id='$id'";
            $results =  mysqli_query( $con, $querys );
            $_SESSION['status'] = 'Approved Successfully!';
            $_SESSION['status_code'] = 'success';
            echo "<script>window.open('admin_AppointmentHistory.php', '_self')</script>";
        } else {
            $_SESSION['status'] = 'Something went wrong!';
            $_SESSION['status_code'] = 'error';
            echo "<script>window.open('admin_AppointmentHistory.php', '_self')</script>";
        }
        
    }

} catch( Exception $e ) {
    echo $e->getMessage();
}
try {
    if ( isset( $_POST['decline'] ) ) {
        $id = $_POST['Id'];
        $fname = $_POST['Fname'];
        $lname = $_POST['Lname'];
        $email = $_POST['Email'];
        $mobile = $_POST['Mobile'];

        $query = "UPDATE appointment SET Fname= '$fname', Lname='$lname',Email= '$email',Mobile='$mobile',stats='DECLINED' WHERE Appointment_Id = '$id' ";
        $query_run =  mysqli_query( $con, $query );
        if ( $query_run ) {
            // echo "<script>alert('Declined Status Updated!')</script>";
            $_SESSION['status'] = 'Declined Successfully!';
            $_SESSION['status_code'] = 'success';
            header( 'location:patientList.php' );
        } else {
            $_SESSION['status'] = 'Something went wrong';
            $_SESSION['status_code'] = 'error';
            header( 'Location:updated.php' );
        }
    }
} catch( Exception $e ) {
    echo $e->getMessage();
}

// function accessAllowed(){
//    $command =  mysql_query("SELECT COUNT(super_admin_id) FROM super_admin_tb");
//    $num_rows = mysql_num_rows($command);
// }
// $stats = accessAllowed();
// if($stats > 0 ){

// }

?>