{include file="header.tpl" title="Group Permission management"}


<div class="uk-container uk-container-expand uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Group permissions</span></h2>

    <form action="admin_groups.php" method="post">
    <table style="width:100%" class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th>Group</th>
                {section name=ap loop=$available_permissions}
                    <th>{$available_permissions[ap].name_pretty}</th>
                {/section}
            </tr>
        </thead>
        <tbody>
            {foreach from=$available_groups item=g}
                <tr>
                    <td>{$g.name}</td>
                    {section name=gap loop=$available_permissions}
                        <td><input type="checkbox" value="1" name="group_{$g.id}_permission_{$available_permissions[gap].name}" {if $group_permission[$g.id][$available_permissions[gap].name]} checked{/if}></td>
                    {/section}
                </tr>
            {/foreach}
        </tbody>
    </table>
        <input type="submit" class="uk-button uk-button-large uk-button-primary" value="SAVE PERMISSIONS" name="save" />
    </form>

</div>



{include file="footer.tpl"}
