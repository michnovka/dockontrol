{include file="header.tpl" title="Create guest pass"}


<div class="uk-container uk-container-large uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Create guest pass</span></h2>

    {if $success_link}
        <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
            <p>Successfully created guest link, find it below</p>
        </div>


        <hr/>
        Share this URL with your guests:
        <div class="uk-margin">
            <input class="uk-input uk-form-width-large" type="text" value="{$success_link}" readonly>
        </div>

        <hr/>

    {/if}

    <form class="uk-form-stacked uk-margin-large uk-text-left" method="post">

        <div class="uk-margin">
            <label class="uk-form-label" for="expires">Expires</label>
            <div class="uk-form-controls">
                <select class="uk-input" id="expires" name="expires">
                    <option value="1">1 hour</option>
                    <option value="24">24 hours</option>
                    <option value="48">2 days</option>
                    <option value="168">1 week</option>
                </select>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="remaining_actions">Allowed number of actions:</label>
            <div class="uk-form-controls">
                <select class="uk-input" id="remaining_actions" name="remaining_actions">
                    <option value="-1">Unlimited</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="uk-margin">
            <div class="uk-form-controls">
                <button type="submit" name="action" value="create" class="uk-button uk-button-primary uk-button-large uk-width-full">Generate</button>
            </div>
        </div>
    </form>



</div>


{include file="footer.tpl"}
