<div class="hero-block"
    style="<% if $BackgroundColor %> background-color: $BackgroundColor; <% end_if %>">
    <% if $BackgroundImage %>
        <div class="hero-block__background">
            $BackgroundImage.Fill(1920, 800)
        </div>
    <% end_if %>

    <div class="hero-block__content hero-block__content--$ContentAlignment">
        <% if $Heading %>
            <h2 class="hero-block__heading"
                style="<% if $TextColor %> color: $TextColor; <% end_if %>">
                $Heading
            </h2>
        <% end_if %>

        <% if $MainContent %>
            <div class="hero-block__main-content"
                style="<% if $TextColor %> color: $TextColor; <% end_if %>">
                $MainContent
            </div>
        <% end_if %>

        <div class="hero-block__links">
            <% if $PrimaryLink %>
                <div class="hero-block__link hero-block__link--primary">
                    $PrimaryLink
                </div>
            <% end_if %>

            <% if $SecondaryLink %>
                <div class="hero-block__link hero-block__link--secondary">
                    $SecondaryLink
                </div>
            <% end_if %>
        </div>
    </div>
</div>
