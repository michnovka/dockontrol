{include file="header.tpl" title="Nodes monitor"}


<div class="uk-container uk-container-expand uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Nodes Monitor</span></h2>

    {if $success}
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{$success}</p>
        </div>
    {/if}

    {if $error}
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{$error}</p>
        </div>
    {/if}

    <table style="width:100%" class="uk-table uk-table-striped uk-table-small">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>IP</th>
                <th>Status</th>
                <th>Last command</th>
                <th>Ping</th>
                <th>Last ping time</th>
                <th>Version</th>
                <th>OS Kernel</th>
                <th>Device</th>
                <th>Uptime</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {section name=n loop=$nodes}
                <tr>
                    <td>{$nodes[n].id}</td>
                    <td>{$nodes[n].name}</td>
                    <td>{$nodes[n].ip}</td>
                    <td><span class="uk-label{if $nodes[n].status != 'pingable'} uk-label-{if $nodes[n].status == 'online'}success{else}danger{/if}{/if}">{$nodes[n].status}</span></td>
                    <td>{$nodes[n].last_command_executed_time}</td>
                    <td>{$nodes[n].ping|number_format:0} ms</td>
                    <td>{$nodes[n].last_ping_time}</td>
                    <td>{$nodes[n].dockontrol_node_version}</td>
                    <td>OS: {$nodes[n].os_version} | Kernel: {$nodes[n].kernel_version}</td>
                    <td>{$nodes[n].device}</td>
                    <td>{($nodes[n].uptime/3600)|number_format:0} hours</td>
                    <td><a href="admin_monitor_nodes.php?action=update_node&node_id={$nodes[n].id}" class="uk-button uk-button-default">UPDATE</a></td>
                </tr>
            {sectionelse}
                <tr><td colspan="11">There are no nodes</td> </tr>
            {/section}
        </tbody>
    </table>

</div>



{include file="footer.tpl"}
