{include file="header.tpl" title="Command queue"}


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Queue</span></h2>

    <h4>Planned</h4>
    <table style="width:100%" class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th>Time start</th>
                <th>Action</th>
                <th>User</th>
                <th>Time created</th>
            </tr>
        </thead>
        <tbody>
            {section name=q loop=$queue}
                <tr>
                    <td>{$queue[q].time_start}</td>
                    <td>{$queue[q].action}</td>
                    <td>{$queue[q].name}</td>
                    <td>{$queue[q].time_created}</td>
                </tr>
            {sectionelse}
                <tr><td colspan="4">There are no actions queued</td> </tr>

            {/section}
        </tbody>
    </table>

    <h4>Executed</h4>
    <table style="width:100%" class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th>Time start</th>
                <th>Action</th>
                <th>User</th>
                <th>Time created</th>
            </tr>
        </thead>
        <tbody>
            {section name=qe loop=$queue_executed}
                <tr>
                    <td>{$queue_executed[qe].time_start}</td>
                    <td>{$queue_executed[qe].action}</td>
                    <td><a href="admin_queue.php?user_id={$queue_executed[qe].user_id}" title="View queue for {$queue_executed[qe].name}">{$queue_executed[qe].name}</a></td>
                    <td>{$queue_executed[qe].time_created}</td>
                </tr>

            {/section}
        </tbody>
    </table>
</div>



{include file="footer.tpl"}
