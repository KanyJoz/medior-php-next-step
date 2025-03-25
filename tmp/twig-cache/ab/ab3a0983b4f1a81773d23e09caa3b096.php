<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* emails/welcome.twig */
class __TwigTemplate_77260bed2745c5de05b3eea257950447 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!doctype html>
<html lang=\"en\">
    <head>
        <title>Welcome</title>
        <meta name=\"viewport\" content=\"width=device-width\" />
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    </head>

    <body>
        <p>Hi,</p>
        <p>Your user ID number is ";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["user"] ?? null), "id", [], "any", false, false, false, 11), "html", null, true);
        yield ".</p>
        <p>Please send a request to the `PUT /v1/users/activated` endpoint with the following JSON
            body to activate your account:</p>
        <p>{\"token\": \"";
        // line 14
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["token"] ?? null), "plainText", [], "any", false, false, false, 14), "html", null, true);
        yield "\"}</p>
        <p>Please note that this is a one-time use token and it will expire in 3 days.</p>
    </body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "emails/welcome.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  60 => 14,  54 => 11,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "emails/welcome.twig", "/Users/Jozsef_Kanyo/Documents/Projects/ani-merged/templates/emails/welcome.twig");
    }
}
