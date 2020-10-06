{include file="header.tpl" title="Usage stats"}


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Statistics</span></h2>

    <h4>Stats for current month</h4>
    <table style="width:100%" class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th>Action</th>
                <th>Counter</th>
            </tr>
        </thead>
        <tbody>
            {section name=us loop=$usage_stats}
                <tr>
                    <td>{$usage_stats[us].action}</td>
                    <td>{$usage_stats[us].c}</td>
                </tr>
            {sectionelse}
                <tr><td colspan="2">There are no actions queued</td> </tr>
            {/section}
        </tbody>
    </table>

</div>



{include file="footer.tpl"}
