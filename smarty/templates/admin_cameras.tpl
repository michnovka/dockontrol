{include file="header.tpl" title="Admin Cameras"}


<div class="uk-container uk-container-expand uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Cameras</span></h2>

    <div class="uk-grid-collapse uk-child-width-expand@s uk-text-center uk-margin-large-top" uk-grid>
        {section name="c" loop=$cameras}
            <div class="uk-width-1-3@m">
                <div class="uk-background-muted"><img src="camera.php?camera={$cameras[c]}" title="{$cameras[c]}" /></div>
            </div>
        {/section}
    </div>

</div>



{include file="footer.tpl"}
