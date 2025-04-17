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

/* base.html.twig */
class __TwigTemplate_913fb2b98e00639c771db3719e16c76b extends Template
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
            'title' => [$this, 'block_title'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>";
        // line 6
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        yield "</title>
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css\" rel=\"stylesheet\">
        <style>
            .sidebar {
                min-height: 100vh;
                background: #343a40;
                color: white;
            }
            .sidebar .nav-link {
                color: white;
                padding: 0.75rem 1rem;
                transition: all 0.3s ease;
            }
            .sidebar .nav-link:hover {
                background: rgba(255,255,255,.1);
                transform: translateX(5px);
            }
            .sidebar .nav-link.active {
                background: #0d6efd;
                color: white;
            }
            .main-content {
                padding: 20px;
            }
            .card {
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                border: none;
                margin-bottom: 20px;
            }
            .table th {
                background-color: #f8f9fa;
            }
            .btn-icon {
                padding: 0.375rem 0.75rem;
            }
        </style>
    </head>
    <body>
        <div class=\"container-fluid\">
            <div class=\"row\">
                <!-- Sidebar -->
                <div class=\"col-md-3 col-lg-2 d-md-block sidebar collapse\">
                    <div class=\"position-sticky pt-3\">
                        <div class=\"text-center mb-4\">
                            <h4>Gestion des Produits</h4>
                        </div>
                        <ul class=\"nav flex-column\">
                            <li class=\"nav-item\">
                                <a class=\"nav-link ";
        // line 55
        yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 55, $this->source); })()), "request", [], "any", false, false, false, 55), "get", ["_route"], "method", false, false, false, 55) == "liste_produits")) ? ("active") : (""));
        yield "\" href=\"";
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("liste_produits");
        yield "\">
                                    <i class=\"bi bi-list-ul me-2\"></i>Liste des Produits
                                </a>
                            </li>
                            <li class=\"nav-item\">
                                <a class=\"nav-link ";
        // line 60
        yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 60, $this->source); })()), "request", [], "any", false, false, false, 60), "get", ["_route"], "method", false, false, false, 60) == "ajouter_produit")) ? ("active") : (""));
        yield "\" href=\"";
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("ajouter_produit");
        yield "\">
                                    <i class=\"bi bi-plus-circle me-2\"></i>Ajouter un Produit
                                </a>
                            </li>
                            <li class=\"nav-item\">
                                <a class=\"nav-link ";
        // line 65
        yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 65, $this->source); })()), "request", [], "any", false, false, false, 65), "get", ["_route"], "method", false, false, false, 65) == "liste_commandes")) ? ("active") : (""));
        yield "\" href=\"";
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("liste_commandes");
        yield "\">
                                    <i class=\"bi bi-cart me-2\"></i>Commandes
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main content -->
                <main class=\"col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content\">
                    ";
        // line 75
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 75, $this->source); })()), "flashes", ["success"], "method", false, false, false, 75));
        foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
            // line 76
            yield "                        <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                            ";
            // line 77
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["message"], "html", null, true);
            yield "
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                        </div>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['message'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 81
        yield "
                    ";
        // line 82
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 82, $this->source); })()), "flashes", ["error"], "method", false, false, false, 82));
        foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
            // line 83
            yield "                        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                            ";
            // line 84
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["message"], "html", null, true);
            yield "
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                        </div>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['message'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 88
        yield "
                    ";
        // line 89
        yield from $this->unwrap()->yieldBlock('body', $context, $blocks);
        // line 90
        yield "                </main>
            </div>
        </div>

        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
        <script>
            // Activer les tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    </body>
</html>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        yield from [];
    }

    // line 6
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        yield "Gestion des Produits";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 89
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_body(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "base.html.twig";
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
        return array (  234 => 89,  211 => 6,  186 => 90,  184 => 89,  181 => 88,  171 => 84,  168 => 83,  164 => 82,  161 => 81,  151 => 77,  148 => 76,  144 => 75,  129 => 65,  119 => 60,  109 => 55,  57 => 6,  50 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>{% block title %}Gestion des Produits{% endblock %}</title>
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css\" rel=\"stylesheet\">
        <style>
            .sidebar {
                min-height: 100vh;
                background: #343a40;
                color: white;
            }
            .sidebar .nav-link {
                color: white;
                padding: 0.75rem 1rem;
                transition: all 0.3s ease;
            }
            .sidebar .nav-link:hover {
                background: rgba(255,255,255,.1);
                transform: translateX(5px);
            }
            .sidebar .nav-link.active {
                background: #0d6efd;
                color: white;
            }
            .main-content {
                padding: 20px;
            }
            .card {
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                border: none;
                margin-bottom: 20px;
            }
            .table th {
                background-color: #f8f9fa;
            }
            .btn-icon {
                padding: 0.375rem 0.75rem;
            }
        </style>
    </head>
    <body>
        <div class=\"container-fluid\">
            <div class=\"row\">
                <!-- Sidebar -->
                <div class=\"col-md-3 col-lg-2 d-md-block sidebar collapse\">
                    <div class=\"position-sticky pt-3\">
                        <div class=\"text-center mb-4\">
                            <h4>Gestion des Produits</h4>
                        </div>
                        <ul class=\"nav flex-column\">
                            <li class=\"nav-item\">
                                <a class=\"nav-link {{ app.request.get('_route') == 'liste_produits' ? 'active' }}\" href=\"{{ path('liste_produits') }}\">
                                    <i class=\"bi bi-list-ul me-2\"></i>Liste des Produits
                                </a>
                            </li>
                            <li class=\"nav-item\">
                                <a class=\"nav-link {{ app.request.get('_route') == 'ajouter_produit' ? 'active' }}\" href=\"{{ path('ajouter_produit') }}\">
                                    <i class=\"bi bi-plus-circle me-2\"></i>Ajouter un Produit
                                </a>
                            </li>
                            <li class=\"nav-item\">
                                <a class=\"nav-link {{ app.request.get('_route') == 'liste_commandes' ? 'active' }}\" href=\"{{ path('liste_commandes') }}\">
                                    <i class=\"bi bi-cart me-2\"></i>Commandes
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main content -->
                <main class=\"col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content\">
                    {% for message in app.flashes('success') %}
                        <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                            {{ message }}
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                        </div>
                    {% endfor %}

                    {% for message in app.flashes('error') %}
                        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                            {{ message }}
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                        </div>
                    {% endfor %}

                    {% block body %}{% endblock %}
                </main>
            </div>
        </div>

        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
        <script>
            // Activer les tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    </body>
</html>
", "base.html.twig", "C:\\Users\\Nassir\\Produit\\templates\\base.html.twig");
    }
}
