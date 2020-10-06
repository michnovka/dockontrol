{include file="header.tpl" title="Usage stats"}


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Statistics</span></h2>

    <h4>Stats for current month</h4>
    <table style="width:100%" class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th>Action</th>
                {section name=usp loop=$usage_stats_periods}
                    <th>{$usage_stats_periods[usp]}</th>
                {/section}
            </tr>
        </thead>
        <tbody>
            {foreach from=$usage_stats item=us key=a}
                <tr>
                    <td>{$a}</td>
                    {foreach from=$usage_stats_periods item=usp}
                        <td>{$us[$usp]|number_format:0}</td>
                    {/foreach}
                </tr>
            {/foreach}
        </tbody>
    </table>

</div>



{include file="footer.tpl"}
