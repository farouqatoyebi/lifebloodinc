<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> Users</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter" id="list-accounts">
                            <thead class="text-primary">
                                <tr>
                                    <th>
                                        S/N
                                    </th>
                                    <th>
                                        Full Name
                                    </th>
                                    <th>
                                        Date of Birth
                                    </th>
                                    <th>
                                        Age
                                    </th>
                                    <th>
                                        Gender
                                    </th>
                                    <th>
                                        Blood Group
                                    </th>
                                    <th>
                                        Genotype
                                    </th>
                                    <th>
                                        Occupation
                                    </th>
                                    <th>
                                        Country
                                    </th>
                                    <th>
                                        State
                                    </th>
                                    <th>
                                        City
                                    </th>
                                    <th>
                                        Donor
                                    </th>
                                    <th class="text-center">
                                        Current Balance
                                    </th>
                                    <th class="text-center">
                                        Book Balance
                                    </th>
                                    <th>
                                        Date Created
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($usersBreakdown) { $counter = 0; ?>
                                    <?php foreach ($usersBreakdown as $value) { ?>
                                        <?php 
                                            $firstName = $value->firstname ? $value->firstname : '';
                                            $otherNames = $value->lastname ? $value->lastname : '';
                                            $LastName = $value->other_names ? $value->other_names : '';

                                            $arrayOfNames = [$firstName, $otherNames, $LastName];
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo ++$counter; ?>
                                            </td>
                                            <td>
                                                <?php echo ucwords(implode(" ", $arrayOfNames)); ?>
                                            </td>
                                            <td>
                                                <?php echo $value->dob ? date("jS F, Y", $value->dob) : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo (isset($value->age) && $value->age) ? $value->age : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->gender ? ucwords($value->gender) : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->blood_group ? $value->blood_group : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->genotype ? $value->genotype : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->occupation ? ucwords($value->occupation) : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->country_id ? $baseController->getLocationValueBasedOnType($value->country_id, 'country') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->state_id ? $baseController->getLocationValueBasedOnType($value->state_id, 'state') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->city_id ? $baseController->getLocationValueBasedOnType($value->city_id, 'city') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->donor ? ucwords($value->donor) : '- - - - -'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo 'NGN '.number_format($value->amount, 2); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo 'NGN '.number_format($value->acct_balance, 2); ?>
                                            </td>
                                            <td>
                                                <?php echo $value->created_at ? date("jS F, Y", $value->created_at) : '- - - - -'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="15" class="text-center">
                                            <p>No records found</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>