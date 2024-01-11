<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> Hospitals</h4>
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
                                        Name
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
                                        Address
                                    </th>
                                    <th>
                                        Registeration Number
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
                                <?php if ($hospitalsBreakdown) { $counter = 0; ?>
                                    <?php foreach ($hospitalsBreakdown as $value) { ?>
                                        <tr>
                                            <td>
                                                <?php echo ++$counter; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->name; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->country ? $baseController->getLocationValueBasedOnType($value->country, 'country') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->state ? $baseController->getLocationValueBasedOnType($value->state, 'state') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->city ? $baseController->getLocationValueBasedOnType($value->city, 'city') : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo $value->Address ? $value->Address : '- - - - -'; ?>
                                            </td>
                                            <td>
                                                <?php echo ($value->reg_no && strpos($value->reg_no, '@') === FALSE) ? $value->reg_no : '- - - - -' ; ?>
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
                                        <td colspan="10" class="text-center">
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