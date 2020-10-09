{include file="header.tpl" title="CRONs"}


<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>CRONS</span></h2>

    <h4>Last executed CRONs</h4>
    <div class="uk-section">
        <table style="width:100%" class="uk-table uk-table-striped">
            <tr>
                <td><strong>Action Queue:</strong></td>
                <td>{if $_CONFIG.last_cron_action_queue_time}{$_CONFIG.last_cron_action_queue_time}{else}NEVER{/if}</td>
            </tr>
            <tr>
                <td><strong>Node Monitor:</strong></td>
                <td>{if $_CONFIG.last_cron_monitor_time}{$_CONFIG.last_cron_monitor_time}{else}NEVER{/if}</td>
            </tr>
            <tr>
                <td><strong>DB cleanup:</strong></td>
                <td>{if $_CONFIG.last_cron_db_cleanup_time}{$_CONFIG.last_cron_db_cleanup_time}{else}NEVER{/if}</td>
            </tr>
            <tr>
                <td><strong>Update camera cache:</strong></td>
                <td>{if $_CONFIG.last_cron_update_camera_cache_time}{$_CONFIG.last_cron_update_camera_cache_time}{else}NEVER{/if}</td>
            </tr>
        </table>
    </div>

    <h4>CRONTAB example</h4>
    <div class="uk-section uk-text-left">
        <pre><code>{$crontab_example}</code></pre>
    </div>

</div>



{include file="footer.tpl"}
