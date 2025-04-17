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

/* commande_form.html.twig */
class __TwigTemplate_46905010b5492b4981e1a523e425ff8f extends Template
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

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "commande_form.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "commande_form.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "commande_form.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 3
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

        yield "Passer une Commande";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 5
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

        // line 6
        yield "    <div class=\"d-flex justify-content-between align-items-center mb-4\">
        <h2 class=\"mb-0\">Passer une Commande</h2>
        <a href=\"";
        // line 8
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("liste_commandes");
        yield "\" class=\"btn btn-outline-secondary\">
            <i class=\"bi bi-arrow-left me-2\"></i>Retour à la liste
        </a>
    </div>

    <div class=\"card\">
        <div class=\"card-body\">
            ";
        // line 15
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock((isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 15, $this->source); })()), 'form_start', ["attr" => ["class" => "needs-validation", "novalidate" => "novalidate"]]);
        yield "
            <div class=\"row\">
                <div class=\"col-md-6\">
                    <div class=\"mb-3\">
                        ";
        // line 19
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 19, $this->source); })()), "nomClient", [], "any", false, false, false, 19), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Nom du Client"]);
        yield "
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-person\"></i></span>
                            ";
        // line 22
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 22, $this->source); })()), "nomClient", [], "any", false, false, false, 22), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                        </div>
                        <div class=\"invalid-feedback\">
                            ";
        // line 25
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 25, $this->source); })()), "nomClient", [], "any", false, false, false, 25), 'errors');
        yield "
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        ";
        // line 30
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 30, $this->source); })()), "adresse", [], "any", false, false, false, 30), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Adresse"]);
        yield "
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-geo-alt\"></i></span>
                            ";
        // line 33
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 33, $this->source); })()), "adresse", [], "any", false, false, false, 33), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                        </div>
                        <div class=\"invalid-feedback\">
                            ";
        // line 36
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 36, $this->source); })()), "adresse", [], "any", false, false, false, 36), 'errors');
        yield "
                        </div>
                    </div>
                </div>

                <div class=\"col-md-6\">
                    <div class=\"mb-3\">
                        ";
        // line 43
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 43, $this->source); })()), "telephone", [], "any", false, false, false, 43), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Téléphone"]);
        yield "
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-telephone\"></i></span>
                            ";
        // line 46
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 46, $this->source); })()), "telephone", [], "any", false, false, false, 46), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                        </div>
                        <div class=\"invalid-feedback\">
                            ";
        // line 49
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 49, $this->source); })()), "telephone", [], "any", false, false, false, 49), 'errors');
        yield "
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        ";
        // line 54
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 54, $this->source); })()), "produit", [], "any", false, false, false, 54), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Produit"]);
        yield "
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-box\"></i></span>
                            ";
        // line 57
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 57, $this->source); })()), "produit", [], "any", false, false, false, 57), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                        </div>
                        <div class=\"invalid-feedback\">
                            ";
        // line 60
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 60, $this->source); })()), "produit", [], "any", false, false, false, 60), 'errors');
        yield "
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        ";
        // line 65
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 65, $this->source); })()), "quantite", [], "any", false, false, false, 65), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Quantité"]);
        yield "
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-cart\"></i></span>
                            ";
        // line 68
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 68, $this->source); })()), "quantite", [], "any", false, false, false, 68), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                        </div>
                        <div class=\"invalid-feedback\">
                            ";
        // line 71
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 71, $this->source); })()), "quantite", [], "any", false, false, false, 71), 'errors');
        yield "
                        </div>
                    </div>
                </div>
            </div>

            <div class=\"d-grid gap-2 d-md-flex justify-content-md-end mt-4\">
                <button type=\"submit\" class=\"btn btn-primary\">
                    <i class=\"bi bi-check-circle me-2\"></i>Passer la commande
                </button>
            </div>
            ";
        // line 82
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock((isset($context["form"]) || array_key_exists("form", $context) ? $context["form"] : (function () { throw new RuntimeError('Variable "form" does not exist.', 82, $this->source); })()), 'form_end', ["render_rest" => false]);
        yield "
        </div>
    </div>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "commande_form.html.twig";
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
        return array (  229 => 82,  215 => 71,  209 => 68,  203 => 65,  195 => 60,  189 => 57,  183 => 54,  175 => 49,  169 => 46,  163 => 43,  153 => 36,  147 => 33,  141 => 30,  133 => 25,  127 => 22,  121 => 19,  114 => 15,  104 => 8,  100 => 6,  87 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Passer une Commande{% endblock %}

{% block body %}
    <div class=\"d-flex justify-content-between align-items-center mb-4\">
        <h2 class=\"mb-0\">Passer une Commande</h2>
        <a href=\"{{ path('liste_commandes') }}\" class=\"btn btn-outline-secondary\">
            <i class=\"bi bi-arrow-left me-2\"></i>Retour à la liste
        </a>
    </div>

    <div class=\"card\">
        <div class=\"card-body\">
            {{ form_start(form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
            <div class=\"row\">
                <div class=\"col-md-6\">
                    <div class=\"mb-3\">
                        {{ form_label(form.nomClient, \"Nom du Client\", {'label_attr': {'class': 'form-label'}}) }}
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-person\"></i></span>
                            {{ form_widget(form.nomClient, {'attr': {'class': 'form-control'}}) }}
                        </div>
                        <div class=\"invalid-feedback\">
                            {{ form_errors(form.nomClient) }}
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        {{ form_label(form.adresse, \"Adresse\", {'label_attr': {'class': 'form-label'}}) }}
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-geo-alt\"></i></span>
                            {{ form_widget(form.adresse, {'attr': {'class': 'form-control'}}) }}
                        </div>
                        <div class=\"invalid-feedback\">
                            {{ form_errors(form.adresse) }}
                        </div>
                    </div>
                </div>

                <div class=\"col-md-6\">
                    <div class=\"mb-3\">
                        {{ form_label(form.telephone, \"Téléphone\", {'label_attr': {'class': 'form-label'}}) }}
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-telephone\"></i></span>
                            {{ form_widget(form.telephone, {'attr': {'class': 'form-control'}}) }}
                        </div>
                        <div class=\"invalid-feedback\">
                            {{ form_errors(form.telephone) }}
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        {{ form_label(form.produit, \"Produit\", {'label_attr': {'class': 'form-label'}}) }}
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-box\"></i></span>
                            {{ form_widget(form.produit, {'attr': {'class': 'form-control'}}) }}
                        </div>
                        <div class=\"invalid-feedback\">
                            {{ form_errors(form.produit) }}
                        </div>
                    </div>

                    <div class=\"mb-3\">
                        {{ form_label(form.quantite, \"Quantité\", {'label_attr': {'class': 'form-label'}}) }}
                        <div class=\"input-group\">
                            <span class=\"input-group-text\"><i class=\"bi bi-cart\"></i></span>
                            {{ form_widget(form.quantite, {'attr': {'class': 'form-control'}}) }}
                        </div>
                        <div class=\"invalid-feedback\">
                            {{ form_errors(form.quantite) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class=\"d-grid gap-2 d-md-flex justify-content-md-end mt-4\">
                <button type=\"submit\" class=\"btn btn-primary\">
                    <i class=\"bi bi-check-circle me-2\"></i>Passer la commande
                </button>
            </div>
            {{ form_end(form, {'render_rest': false}) }}
        </div>
    </div>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
{% endblock %} ", "commande_form.html.twig", "C:\\Users\\Nassir\\Produit\\templates\\commande_form.html.twig");
    }
}
