import { useState } from "react";
import {
  Search, BookOpen, Download, Eye, Bell, ChevronRight,
  Users, FileText, CheckCircle, XCircle, Clock, AlertCircle,
  Settings, LogOut, Plus, Filter, Shield, UserCheck, UserX,
  TrendingUp, Library, Home, Upload, MessageSquare, RefreshCw,
  Edit2, Trash2, Archive, Activity, User, MoreVertical, Star,
  ChevronDown, Send,
} from "lucide-react";
import {
  AreaChart, Area, BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, PieChart, Pie, Cell,
} from "recharts";

// ─── Types ────────────────────────────────────────────────────────────────────

type Role = "visitor" | "user" | "hr" | "manager" | "admin";

interface SidebarItem {
  id: string;
  label: string;
  icon: React.ElementType;
  badge?: number;
}

// ─── Mock Data ────────────────────────────────────────────────────────────────

const catalogBooks = [
  { id: 1, title: "Introduction au droit administratif", author: "Jean-Claude Bonardi", year: 2022, category: "Droit", cover: "https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=200&h=280&fit=crop&auto=format", views: 842, downloads: 213 },
  { id: 2, title: "Économie politique africaine", author: "Amina Touré", year: 2021, category: "Économie", cover: "https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=200&h=280&fit=crop&auto=format", views: 1204, downloads: 387 },
  { id: 3, title: "Histoire du Bénin précolonial", author: "Didier Aplogan", year: 2020, category: "Histoire", cover: "https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=200&h=280&fit=crop&auto=format", views: 976, downloads: 294 },
  { id: 4, title: "Mathématiques pour ingénieurs", author: "Paul Segla", year: 2023, category: "Sciences", cover: "https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=200&h=280&fit=crop&auto=format", views: 531, downloads: 178 },
  { id: 5, title: "Littérature francophone contemporaine", author: "Marie-Claire Ahoho", year: 2022, category: "Littérature", cover: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=200&h=280&fit=crop&auto=format", views: 688, downloads: 201 },
  { id: 6, title: "Santé publique et épidémiologie", author: "Rodrigue Gbédji", year: 2023, category: "Médecine", cover: "https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=200&h=280&fit=crop&auto=format", views: 445, downloads: 132 },
  { id: 7, title: "Philosophie africaine moderne", author: "Célestin Hounsou", year: 2021, category: "Philosophie", cover: "https://images.unsplash.com/photo-1512820790803-83ca734da794?w=200&h=280&fit=crop&auto=format", views: 320, downloads: 98 },
  { id: 8, title: "Agriculture durable au Sahel", author: "Koffi Mensah", year: 2022, category: "Sciences", cover: "https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=200&h=280&fit=crop&auto=format", views: 567, downloads: 214 },
];

const depositRequests = [
  { id: 1, title: "Gouvernance locale et développement durable", author: "Fatou Camara", submitted: "20 jan. 2024", status: "pending", manager: null, justification: null },
  { id: 2, title: "Linguistique et langues nationales du Bénin", author: "Ibrahim Koné", submitted: "10 jan. 2024", status: "approved", manager: "M. Dossou", justification: "Document de qualité, bien référencé et pertinent pour le fonds." },
  { id: 3, title: "Guide pratique de comptabilité OHADA", author: "Amine Diallo", submitted: "5 jan. 2024", status: "rejected", manager: "M. Houénou", justification: "Le document ne respecte pas les normes de citation requises et présente des lacunes bibliographiques importantes." },
  { id: 4, title: "Biologie cellulaire avancée — Tome II", author: "Sandrine Amoussou", submitted: "18 jan. 2024", status: "second_opinion", manager: "M. Dossou", justification: "Premier avis favorable — transmis pour second examen indépendant." },
  { id: 5, title: "Droit commercial et affaires internationales", author: "Thomas Kpinsoté", submitted: "12 jan. 2024", status: "published", manager: "Mme Akpovi", justification: "Approuvé et publié avec succès." },
];

const allUsers = [
  { id: 1, name: "Fatou Camara", email: "fatou.camara@email.bj", role: "user", status: "active", joined: "12 sep. 2023", requests: 3 },
  { id: 2, name: "Ibrahim Koné", email: "ibrahim.kone@email.bj", role: "user", status: "active", joined: "4 nov. 2023", requests: 1 },
  { id: 3, name: "Amine Diallo", email: "amine.diallo@email.bj", role: "user", status: "inactive", joined: "22 juil. 2023", requests: 2 },
  { id: 4, name: "Sandrine Amoussou", email: "sandrine.amoussou@email.bj", role: "user", status: "active", joined: "3 jan. 2024", requests: 4 },
  { id: 5, name: "Thomas Kpinsoté", email: "thomas.kpinsote@email.bj", role: "user", status: "active", joined: "15 oct. 2023", requests: 5 },
  { id: 6, name: "Marie Agbodossou", email: "marie.agbodossou@email.bj", role: "manager_hr", status: "active", joined: "1 jun. 2022", requests: 0 },
  { id: 7, name: "Pierre Houénou", email: "pierre.houenou@email.bj", role: "manager_requests", status: "active", joined: "15 mar. 2022", requests: 0 },
];

const statsData = [
  { month: "Juil", publiées: 8, refusées: 4 },
  { month: "Août", publiées: 14, refusées: 4 },
  { month: "Sep", publiées: 18, refusées: 6 },
  { month: "Oct", publiées: 11, refusées: 4 },
  { month: "Nov", publiées: 22, refusées: 6 },
  { month: "Déc", publiées: 17, refusées: 4 },
  { month: "Jan", publiées: 26, refusées: 8 },
];

const userGrowth = [
  { month: "Juil", inscrits: 38 },
  { month: "Août", inscrits: 52 },
  { month: "Sep", inscrits: 67 },
  { month: "Oct", inscrits: 44 },
  { month: "Nov", inscrits: 81 },
  { month: "Déc", inscrits: 63 },
  { month: "Jan", inscrits: 127 },
];

const pieData = [
  { name: "Droit", value: 24, color: "#1B4332" },
  { name: "Sciences", value: 18, color: "#2D6A4F" },
  { name: "Histoire", value: 15, color: "#40916C" },
  { name: "Médecine", value: 12, color: "#74C69D" },
  { name: "Littérature", value: 10, color: "#C17D0E" },
  { name: "Autre", value: 21, color: "#E8DFC8" },
];

// ─── Status Badge ─────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; color: string }> = {
  pending: { label: "En attente", color: "bg-amber-100 text-amber-800 border border-amber-200" },
  approved: { label: "Validée", color: "bg-emerald-100 text-emerald-800 border border-emerald-200" },
  rejected: { label: "Refusée", color: "bg-red-100 text-red-800 border border-red-200" },
  published: { label: "Publiée", color: "bg-blue-100 text-blue-800 border border-blue-200" },
  second_opinion: { label: "Second avis", color: "bg-purple-100 text-purple-800 border border-purple-200" },
  active: { label: "Actif", color: "bg-emerald-100 text-emerald-800 border border-emerald-200" },
  inactive: { label: "Inactif", color: "bg-stone-100 text-stone-500 border border-stone-200" },
};

function StatusBadge({ status }: { status: string }) {
  const cfg = statusConfig[status] ?? statusConfig.pending;
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ${cfg.color}`}>
      {cfg.label}
    </span>
  );
}

// ─── Stat Card ────────────────────────────────────────────────────────────────

function StatCard({
  icon: Icon, label, value, trend, bg,
}: {
  icon: React.ElementType;
  label: string;
  value: string | number;
  trend?: string;
  bg: string;
}) {
  return (
    <div className="bg-card border border-border rounded-xl p-5 flex items-start gap-4">
      <div className={`w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0 ${bg}`}>
        <Icon size={19} className="text-white" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">{label}</p>
        <p
          className="text-2xl font-bold text-foreground mt-0.5"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          {value}
        </p>
        {trend && (
          <p className="text-xs text-emerald-600 mt-1 flex items-center gap-1">
            <TrendingUp size={11} />
            {trend}
          </p>
        )}
      </div>
    </div>
  );
}

// ─── Sidebar ──────────────────────────────────────────────────────────────────

function Sidebar({
  items, active, onNavigate, userName, roleLabel,
}: {
  items: SidebarItem[];
  active: string;
  onNavigate: (id: string) => void;
  userName: string;
  roleLabel: string;
}) {
  const initials = userName
    .split(" ")
    .map((n) => n[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();

  return (
    <div
      className="w-60 flex-shrink-0 flex flex-col"
      style={{ backgroundColor: "#1B4332" }}
    >
      {/* Logo */}
      <div className="px-5 py-5 border-b border-white/10">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
            <Library size={15} className="text-white" />
          </div>
          <div>
            <p
              className="text-white font-semibold text-sm leading-tight"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              BibliNum
            </p>
            <p className="text-white/40 text-[11px]">Bibliothèque Nationale</p>
          </div>
        </div>
      </div>

      {/* Nav */}
      <nav className="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        {items.map((item) => (
          <button
            key={item.id}
            onClick={() => onNavigate(item.id)}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors text-left ${
              active === item.id
                ? "bg-white/20 text-white font-medium"
                : "text-white/55 hover:text-white hover:bg-white/10"
            }`}
          >
            <item.icon size={16} className="flex-shrink-0" />
            <span className="flex-1 text-left">{item.label}</span>
            {item.badge !== undefined && item.badge > 0 && (
              <span className="bg-amber-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center leading-none">
                {item.badge}
              </span>
            )}
          </button>
        ))}
      </nav>

      {/* User footer */}
      <div className="px-4 py-4 border-t border-white/10">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
            <span className="text-white text-xs font-semibold">{initials}</span>
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-white text-xs font-medium truncate">{userName}</p>
            <p className="text-white/40 text-[11px] truncate">{roleLabel}</p>
          </div>
          <button className="text-white/30 hover:text-white/70 transition-colors flex-shrink-0">
            <LogOut size={14} />
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── App Shell ────────────────────────────────────────────────────────────────

function AppShell({
  sidebarItems, activeNav, onNavigate, userName, roleLabel, children,
}: {
  sidebarItems: SidebarItem[];
  activeNav: string;
  onNavigate: (id: string) => void;
  userName: string;
  roleLabel: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex" style={{ minHeight: "calc(100vh - 48px)", backgroundColor: "#F6F2EA" }}>
      <Sidebar
        items={sidebarItems}
        active={activeNav}
        onNavigate={onNavigate}
        userName={userName}
        roleLabel={roleLabel}
      />
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Topbar */}
        <header className="h-14 bg-card border-b border-border flex items-center justify-between px-6 flex-shrink-0">
          <div className="flex items-center gap-2.5">
            <Search size={15} className="text-muted-foreground" />
            <input
              placeholder="Rechercher dans le catalogue..."
              className="text-sm bg-transparent outline-none text-foreground placeholder:text-muted-foreground w-72"
            />
          </div>
          <div className="flex items-center gap-4">
            <button className="relative text-muted-foreground hover:text-foreground transition-colors">
              <Bell size={18} />
              <span className="absolute -top-1 -right-1 w-4 h-4 bg-amber-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold">
                3
              </span>
            </button>
            <div className="h-5 w-px bg-border" />
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
              <span className="hidden sm:inline">{userName}</span>
              <ChevronDown size={14} />
            </div>
          </div>
        </header>
        {/* Main */}
        <main className="flex-1 overflow-auto p-6">
          {children}
        </main>
      </div>
    </div>
  );
}

// ─── VISITOR PAGE ─────────────────────────────────────────────────────────────

function VisitorPage() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("Tout");
  const categories = ["Tout", "Droit", "Économie", "Histoire", "Sciences", "Littérature", "Médecine", "Philosophie"];

  const filtered = catalogBooks.filter(
    (b) =>
      (category === "Tout" || b.category === category) &&
      (b.title.toLowerCase().includes(query.toLowerCase()) ||
        b.author.toLowerCase().includes(query.toLowerCase()))
  );

  return (
    <div style={{ backgroundColor: "#F6F2EA", minHeight: "100vh" }}>
      {/* Navbar */}
      <nav
        className="sticky top-0 z-30 border-b border-border"
        style={{ backgroundColor: "rgba(246,242,234,0.92)", backdropFilter: "blur(12px)" }}
      >
        <div className="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Library size={20} style={{ color: "#1B4332" }} />
            <span
              className="text-lg font-bold"
              style={{ fontFamily: "'Playfair Display', serif", color: "#1B4332" }}
            >
              BibliNum
            </span>
            <span className="text-muted-foreground text-sm hidden md:block">
              · Bibliothèque Numérique Nationale
            </span>
          </div>
          <div className="flex items-center gap-4">
            <button className="text-sm text-muted-foreground hover:text-foreground transition-colors hidden sm:block">
              Catalogue
            </button>
            <button className="text-sm text-muted-foreground hover:text-foreground transition-colors hidden sm:block">
              Recherche avancée
            </button>
            <button className="px-4 py-2 text-sm rounded-lg border border-border hover:bg-muted transition-colors font-medium">
              Connexion
            </button>
            <button
              className="px-4 py-2 text-sm rounded-lg text-white font-medium transition-opacity hover:opacity-90"
              style={{ backgroundColor: "#1B4332" }}
            >
              S'inscrire
            </button>
          </div>
        </div>
      </nav>

      {/* Hero */}
      <section
        className="py-24 px-6 relative overflow-hidden"
        style={{ background: "linear-gradient(135deg, #1B4332 0%, #2D6A4F 60%, #40916C 100%)" }}
      >
        <div
          className="absolute inset-0 opacity-10"
          style={{
            backgroundImage: `url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1600&h=600&fit=crop&auto=format")`,
            backgroundSize: "cover",
            backgroundPosition: "center",
          }}
        />
        <div className="relative max-w-4xl mx-auto text-center">
          <p className="text-white/60 text-xs tracking-[0.2em] uppercase mb-5 font-medium">
            Accès libre à la connaissance
          </p>
          <h1
            className="text-white mb-6 leading-[1.15]"
            style={{
              fontFamily: "'Playfair Display', serif",
              fontSize: "clamp(2.2rem, 5vw, 3.75rem)",
            }}
          >
            Le savoir académique,<br />à portée de main
          </h1>
          <p className="text-white/65 text-lg mb-10 max-w-xl mx-auto leading-relaxed">
            Des milliers d'ouvrages, thèses, manuels et articles scientifiques librement accessibles depuis n'importe quel appareil.
          </p>
          {/* Search bar */}
          <div className="flex items-center bg-white rounded-xl shadow-2xl overflow-hidden max-w-2xl mx-auto">
            <Search size={17} className="ml-5 text-muted-foreground flex-shrink-0" />
            <input
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Titre, auteur, mots-clés..."
              className="flex-1 px-4 py-4 text-base outline-none bg-transparent text-foreground placeholder:text-muted-foreground"
            />
            <button
              className="px-6 py-4 text-white text-sm font-medium flex-shrink-0 transition-opacity hover:opacity-90"
              style={{ backgroundColor: "#1B4332" }}
            >
              Rechercher
            </button>
          </div>
          {/* Key numbers */}
          <div className="flex items-center justify-center gap-12 mt-10">
            {[
              ["1 247", "Références"],
              ["38", "Catégories"],
              ["12 800+", "Utilisateurs"],
            ].map(([v, l]) => (
              <div key={l} className="text-center">
                <p
                  className="text-white text-2xl font-bold"
                  style={{ fontFamily: "'Playfair Display', serif" }}
                >
                  {v}
                </p>
                <p className="text-white/50 text-xs mt-0.5">{l}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Category pills */}
      <div className="max-w-7xl mx-auto px-6 pt-8 pb-4">
        <div className="flex items-center gap-2 flex-wrap">
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setCategory(cat)}
              className={`px-4 py-1.5 rounded-full text-sm transition-all font-medium ${
                category === cat
                  ? "text-white shadow-sm"
                  : "bg-card border border-border text-muted-foreground hover:text-foreground hover:border-foreground/20"
              }`}
              style={category === cat ? { backgroundColor: "#1B4332" } : {}}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Catalog grid */}
      <div className="max-w-7xl mx-auto px-6 pb-20">
        <div className="flex items-center justify-between mb-5">
          <p className="text-sm text-muted-foreground">
            <span
              className="font-semibold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              {filtered.length}
            </span>{" "}
            référence{filtered.length !== 1 ? "s" : ""} disponible{filtered.length !== 1 ? "s" : ""}
          </p>
          <button className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <Filter size={14} />
            Filtres avancés
          </button>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
          {filtered.map((book) => (
            <div
              key={book.id}
              className="bg-card rounded-xl overflow-hidden border border-border hover:shadow-lg transition-all duration-200 group cursor-pointer"
            >
              <div className="aspect-[3/4] bg-muted overflow-hidden">
                <img
                  src={book.cover}
                  alt={book.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
              </div>
              <div className="p-3">
                <span className="text-[10px] text-muted-foreground font-medium uppercase tracking-wide">
                  {book.category}
                </span>
                <h3 className="text-xs font-semibold text-foreground mt-1 leading-snug line-clamp-2">
                  {book.title}
                </h3>
                <p className="text-[11px] text-muted-foreground mt-1 truncate">{book.author}</p>
                <div className="flex items-center gap-3 mt-2 text-[11px] text-muted-foreground">
                  <span className="flex items-center gap-0.5">
                    <Eye size={11} />
                    {book.views.toLocaleString("fr")}
                  </span>
                  <span className="flex items-center gap-0.5">
                    <Download size={11} />
                    {book.downloads}
                  </span>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Registration CTA */}
        <div
          className="mt-14 rounded-2xl p-10 text-center border border-border"
          style={{ background: "linear-gradient(135deg, #1B4332 0%, #2D6A4F 100%)" }}
        >
          <Library size={32} className="mx-auto mb-4 text-white/70" />
          <h3
            className="text-2xl font-semibold text-white mb-2"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Accès complet aux ressources
          </h3>
          <p className="text-white/60 mb-6 max-w-md mx-auto text-sm leading-relaxed">
            Inscrivez-vous gratuitement pour lire les documents en ligne, les télécharger et proposer vos propres références documentaires.
          </p>
          <button className="px-7 py-3 bg-white rounded-xl text-sm font-semibold transition-opacity hover:opacity-90" style={{ color: "#1B4332" }}>
            Créer un compte gratuit
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── USER VIEWS ───────────────────────────────────────────────────────────────

function UserSubmitForm({ onBack }: { onBack: () => void }) {
  return (
    <div>
      <button
        onClick={onBack}
        className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-6 transition-colors"
      >
        ← Retour au tableau de bord
      </button>
      <div className="max-w-2xl">
        <h1
          className="text-2xl font-bold text-foreground mb-1"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          Soumettre une référence
        </h1>
        <p className="text-muted-foreground mb-8 text-sm">
          Proposez un nouveau document à intégrer au catalogue de la bibliothèque.
        </p>

        <div className="bg-card rounded-xl border border-border p-6 space-y-5">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Titre du document *</label>
              <input
                className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background placeholder:text-muted-foreground"
                placeholder="ex. Introduction au droit administratif"
              />
            </div>
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Auteur(s) *</label>
              <input
                className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background placeholder:text-muted-foreground"
                placeholder="Nom, Prénom"
              />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Catégorie *</label>
              <select className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background text-foreground">
                <option value="">Choisir une catégorie</option>
                {["Droit", "Sciences", "Histoire", "Médecine", "Littérature", "Économie", "Philosophie"].map((c) => (
                  <option key={c}>{c}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Année de publication *</label>
              <input
                type="number"
                className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background placeholder:text-muted-foreground"
                placeholder="2024"
              />
            </div>
          </div>
          <div>
            <label className="text-sm font-medium text-foreground mb-1.5 block">Éditeur / Maison d'édition</label>
            <input
              className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background placeholder:text-muted-foreground"
              placeholder="Nom de l'éditeur"
            />
          </div>
          <div>
            <label className="text-sm font-medium text-foreground mb-1.5 block">Mots-clés</label>
            <input
              className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background placeholder:text-muted-foreground"
              placeholder="droit public, administration, Bénin... (séparés par des virgules)"
            />
          </div>
          <div>
            <label className="text-sm font-medium text-foreground mb-1.5 block">Résumé</label>
            <textarea
              rows={4}
              className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background resize-none placeholder:text-muted-foreground"
              placeholder="Décrivez brièvement le contenu, l'intérêt et le public cible du document..."
            />
          </div>
          <div>
            <label className="text-sm font-medium text-foreground mb-1.5 block">
              Fichier du document (PDF, max 50 Mo) *
            </label>
            <div className="border-2 border-dashed border-border rounded-xl p-8 text-center cursor-pointer hover:bg-muted/30 transition-colors group">
              <Upload size={24} className="mx-auto mb-2 text-muted-foreground group-hover:text-foreground transition-colors" />
              <p className="text-sm text-muted-foreground">
                Glissez-déposez votre fichier ou{" "}
                <span style={{ color: "#1B4332" }} className="font-medium cursor-pointer">
                  parcourez
                </span>
              </p>
              <p className="text-xs text-muted-foreground mt-1">PDF uniquement · 50 Mo maximum</p>
            </div>
          </div>
          <div className="flex gap-3 pt-2">
            <button
              className="flex-1 py-2.5 text-white rounded-lg text-sm font-medium transition-opacity hover:opacity-90"
              style={{ backgroundColor: "#1B4332" }}
            >
              Soumettre la demande
            </button>
            <button
              onClick={onBack}
              className="px-5 py-2.5 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function UserDashboard({ nav, setNav }: { nav: string; setNav: (n: string) => void }) {
  if (nav === "submit") {
    return <UserSubmitForm onBack={() => setNav("dashboard")} />;
  }

  return (
    <div>
      <div className="mb-7">
        <h1
          className="text-2xl font-bold text-foreground"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          Bonjour, Fatou 👋
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Suivez vos demandes et accédez à vos ressources.
        </p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard icon={FileText} label="Mes demandes" value={5} bg="bg-[#1B4332]" />
        <StatCard icon={Clock} label="En attente" value={2} bg="bg-amber-500" />
        <StatCard icon={CheckCircle} label="Publiées" value={2} bg="bg-emerald-600" />
        <StatCard icon={XCircle} label="Refusées" value={1} bg="bg-red-500" />
      </div>

      {/* Actions */}
      <div className="flex gap-3 mb-8">
        <button
          onClick={() => setNav("submit")}
          className="flex items-center gap-2 px-5 py-2.5 text-white rounded-lg text-sm font-medium transition-opacity hover:opacity-90"
          style={{ backgroundColor: "#1B4332" }}
        >
          <Plus size={15} />
          Soumettre une référence
        </button>
        <button className="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium border border-border hover:bg-muted transition-colors">
          <BookOpen size={15} />
          Explorer le catalogue
        </button>
      </div>

      {/* Requests list */}
      <div className="bg-card rounded-xl border border-border">
        <div className="px-5 py-4 border-b border-border flex items-center justify-between">
          <h2
            className="font-semibold text-foreground"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Mes demandes de dépôt
          </h2>
          <button className="text-xs text-muted-foreground hover:text-foreground transition-colors">
            Voir tout
          </button>
        </div>
        <div className="divide-y divide-border">
          {depositRequests.map((req) => (
            <div key={req.id} className="px-5 py-4 hover:bg-muted/20 transition-colors">
              <div className="flex items-start justify-between gap-3">
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-foreground truncate">{req.title}</p>
                  <div className="flex items-center gap-3 mt-1 flex-wrap">
                    <span className="text-xs text-muted-foreground">Soumis le {req.submitted}</span>
                    {req.manager && (
                      <span className="text-xs text-muted-foreground">· Examiné par {req.manager}</span>
                    )}
                  </div>
                  {req.status === "rejected" && req.justification && (
                    <div className="mt-2 p-2.5 bg-red-50 border border-red-100 rounded-lg text-xs text-red-700 leading-relaxed">
                      <span className="font-semibold">Motif du refus : </span>
                      {req.justification}
                    </div>
                  )}
                  {req.status === "approved" && req.justification && (
                    <div className="mt-2 p-2.5 bg-emerald-50 border border-emerald-100 rounded-lg text-xs text-emerald-700">
                      {req.justification}
                    </div>
                  )}
                </div>
                <StatusBadge status={req.status} />
              </div>

              {/* Progress dots */}
              <div className="flex items-center gap-1.5 mt-3">
                {["Soumission", "Examen", "Décision", "Publication"].map((step, i) => {
                  const progress =
                    req.status === "published"
                      ? 4
                      : req.status === "approved"
                      ? 3
                      : req.status === "rejected"
                      ? 2
                      : req.status === "second_opinion"
                      ? 2
                      : 1;
                  const filled = i < progress;
                  const current = i === progress - 1;
                  return (
                    <div key={step} className="flex items-center">
                      <div
                        className={`w-2 h-2 rounded-full transition-colors ${
                          filled ? "bg-[#1B4332]" : "bg-border"
                        } ${current ? "ring-2 ring-[#1B4332]/30" : ""}`}
                      />
                      {i < 3 && (
                        <div
                          className={`h-px w-6 transition-colors ${
                            i < progress - 1 ? "bg-[#1B4332]" : "bg-border"
                          }`}
                        />
                      )}
                    </div>
                  );
                })}
                <span className="ml-2 text-[11px] text-muted-foreground">
                  {["Soumission", "Examen", "Décision", "Publication"][
                    req.status === "published"
                      ? 3
                      : req.status === "approved"
                      ? 2
                      : req.status === "rejected"
                      ? 2
                      : req.status === "second_opinion"
                      ? 1
                      : 0
                  ]}
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ─── HR MANAGER VIEWS ─────────────────────────────────────────────────────────

function HRDashboard({ nav }: { nav: string }) {
  if (nav === "users") {
    return (
      <div>
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1
              className="text-2xl font-bold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              Gestion des utilisateurs
            </h1>
            <p className="text-muted-foreground mt-1 text-sm">{allUsers.length} comptes enregistrés</p>
          </div>
          <button
            className="flex items-center gap-2 px-5 py-2.5 text-white rounded-lg text-sm font-medium transition-opacity hover:opacity-90"
            style={{ backgroundColor: "#1B4332" }}
          >
            <Plus size={15} />
            Créer un compte
          </button>
        </div>

        <div className="flex gap-3 mb-5">
          <div className="flex-1 flex items-center gap-2 bg-card border border-border rounded-lg px-3 py-2.5">
            <Search size={14} className="text-muted-foreground" />
            <input
              placeholder="Rechercher un utilisateur..."
              className="flex-1 text-sm outline-none bg-transparent text-foreground placeholder:text-muted-foreground"
            />
          </div>
          <select className="px-3 py-2 border border-border rounded-lg text-sm bg-card text-foreground outline-none">
            <option>Tous les rôles</option>
            <option>Utilisateur</option>
            <option>Responsable RH</option>
            <option>Resp. demandes</option>
          </select>
          <select className="px-3 py-2 border border-border rounded-lg text-sm bg-card text-foreground outline-none">
            <option>Tous les statuts</option>
            <option>Actif</option>
            <option>Inactif</option>
          </select>
        </div>

        <div className="bg-card rounded-xl border border-border overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border" style={{ backgroundColor: "rgba(235,228,212,0.4)" }}>
                {["Utilisateur", "Rôle", "Statut", "Inscrit le", "Demandes", "Actions"].map((h) => (
                  <th
                    key={h}
                    className="px-5 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-wider"
                  >
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {allUsers.map((user) => (
                <tr key={user.id} className="hover:bg-muted/20 transition-colors">
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-3">
                      <div
                        className="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
                        style={{ backgroundColor: "#2D6A4F" }}
                      >
                        {user.name
                          .split(" ")
                          .map((n) => n[0])
                          .join("")
                          .slice(0, 2)}
                      </div>
                      <div>
                        <p className="text-sm font-medium text-foreground">{user.name}</p>
                        <p className="text-xs text-muted-foreground">{user.email}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-5 py-3.5 text-sm text-foreground">
                    {user.role === "user"
                      ? "Utilisateur"
                      : user.role === "manager_hr"
                      ? "Resp. RH"
                      : "Resp. Demandes"}
                  </td>
                  <td className="px-5 py-3.5">
                    <StatusBadge status={user.status} />
                  </td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground">{user.joined}</td>
                  <td className="px-5 py-3.5 text-sm text-foreground">{user.requests}</td>
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-1">
                      <button className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors">
                        <Edit2 size={14} />
                      </button>
                      <button
                        className={`p-1.5 rounded-lg transition-colors ${
                          user.status === "active"
                            ? "text-red-400 hover:text-red-600 hover:bg-red-50"
                            : "text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50"
                        }`}
                      >
                        {user.status === "active" ? <UserX size={14} /> : <UserCheck size={14} />}
                      </button>
                      <button className="p-1.5 text-muted-foreground hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                        <Trash2 size={14} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          <div className="px-5 py-3 border-t border-border flex items-center justify-between">
            <p className="text-xs text-muted-foreground">7 utilisateurs au total</p>
            <div className="flex items-center gap-2">
              <button className="px-3 py-1.5 text-xs border border-border rounded-lg hover:bg-muted transition-colors">Précédent</button>
              <span className="text-xs text-foreground px-2">1 / 1</span>
              <button className="px-3 py-1.5 text-xs border border-border rounded-lg hover:bg-muted transition-colors">Suivant</button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // Default HR dashboard
  return (
    <div>
      <div className="mb-7">
        <h1
          className="text-2xl font-bold text-foreground"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          Tableau de bord — RH
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">Gestion des comptes et des accès utilisateurs</p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard icon={Users} label="Total utilisateurs" value={1247} trend="+34 ce mois" bg="bg-[#1B4332]" />
        <StatCard icon={UserCheck} label="Comptes actifs" value={1198} bg="bg-emerald-600" />
        <StatCard icon={UserX} label="Comptes inactifs" value={49} bg="bg-amber-500" />
        <StatCard icon={Plus} label="Nouveaux ce mois" value={34} trend="+18% vs nov." bg="bg-blue-600" />
      </div>

      <div className="grid grid-cols-3 gap-6">
        <div className="col-span-2 bg-card rounded-xl border border-border p-5">
          <h3
            className="font-semibold text-foreground mb-4"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Inscriptions mensuelles
          </h3>
          <ResponsiveContainer width="100%" height={220}>
            <AreaChart data={userGrowth} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="rgba(28,25,22,0.06)" />
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6E6249" }} />
              <YAxis tick={{ fontSize: 11, fill: "#6E6249" }} />
              <Tooltip
                contentStyle={{ backgroundColor: "#FDFAF3", border: "1px solid rgba(28,25,22,0.1)", borderRadius: 8 }}
              />
              <Area
                type="monotone"
                dataKey="inscrits"
                name="Inscriptions"
                stroke="#1B4332"
                fill="rgba(27,67,50,0.12)"
                strokeWidth={2}
              />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-xl border border-border p-5">
          <h3
            className="font-semibold text-foreground mb-4"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Actions récentes
          </h3>
          <div className="space-y-3.5">
            {[
              { action: "Compte activé", user: "K. Amoussou", time: "Il y a 2h", type: "success" },
              { action: "Compte créé", user: "P. Mensah", time: "Il y a 5h", type: "success" },
              { action: "Compte suspendu", user: "A. Diallo", time: "Hier 14h22", type: "warning" },
              { action: "Rôle modifié", user: "T. Kpinsoté", time: "Hier 09h10", type: "info" },
              { action: "Mdp réinitialisé", user: "F. Camara", time: "Il y a 3j", type: "info" },
            ].map((item, i) => (
              <div key={i} className="flex items-start gap-3">
                <div
                  className={`w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1.5 ${
                    item.type === "success"
                      ? "bg-emerald-500"
                      : item.type === "warning"
                      ? "bg-amber-500"
                      : "bg-blue-500"
                  }`}
                />
                <div className="flex-1 min-w-0">
                  <p className="text-xs text-foreground leading-snug">
                    <span className="font-medium">{item.action}</span>{" "}
                    <span className="text-muted-foreground">— {item.user}</span>
                  </p>
                  <p className="text-[11px] text-muted-foreground mt-0.5">{item.time}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── REQUEST MANAGER VIEWS ────────────────────────────────────────────────────

function ManagerDashboard({ nav }: { nav: string }) {
  const [selectedId, setSelectedId] = useState<number>(1);
  const [decision, setDecision] = useState<"approve" | "reject" | null>(null);
  const [justification, setJustification] = useState("");

  const req = depositRequests.find((r) => r.id === selectedId);

  if (nav === "requests") {
    return (
      <div>
        <div className="mb-6">
          <h1
            className="text-2xl font-bold text-foreground"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Demandes assignées
          </h1>
          <p className="text-muted-foreground mt-1 text-sm">5 demandes en attente d'examen</p>
        </div>

        <div className="grid gap-5" style={{ gridTemplateColumns: "1fr 1.7fr", height: "calc(100vh - 220px)" }}>
          {/* List */}
          <div className="bg-card rounded-xl border border-border flex flex-col overflow-hidden">
            <div className="px-4 py-3 border-b border-border flex-shrink-0">
              <div className="flex items-center gap-2 bg-muted rounded-lg px-3 py-2">
                <Search size={13} className="text-muted-foreground" />
                <input
                  placeholder="Filtrer les demandes..."
                  className="flex-1 text-sm outline-none bg-transparent text-foreground placeholder:text-muted-foreground"
                />
              </div>
            </div>
            <div className="flex-1 overflow-y-auto">
              {depositRequests.map((r) => (
                <button
                  key={r.id}
                  onClick={() => {
                    setSelectedId(r.id);
                    setDecision(null);
                    setJustification("");
                  }}
                  className={`w-full text-left px-4 py-3.5 border-b border-border last:border-0 transition-all ${
                    selectedId === r.id
                      ? "border-l-2 border-l-[#1B4332] pl-3.5"
                      : "hover:bg-muted/30"
                  }`}
                  style={selectedId === r.id ? { backgroundColor: "rgba(27,67,50,0.05)" } : {}}
                >
                  <div className="flex items-start justify-between gap-2 mb-1">
                    <p className="text-sm font-medium text-foreground line-clamp-2 leading-snug flex-1">
                      {r.title}
                    </p>
                  </div>
                  <div className="flex items-center justify-between">
                    <p className="text-xs text-muted-foreground">{r.author} · {r.submitted}</p>
                    <StatusBadge status={r.status} />
                  </div>
                </button>
              ))}
            </div>
          </div>

          {/* Review panel */}
          <div className="bg-card rounded-xl border border-border flex flex-col overflow-hidden">
            {req ? (
              <>
                <div className="px-6 py-5 border-b border-border flex-shrink-0">
                  <div className="flex items-start justify-between gap-3">
                    <div className="flex-1 min-w-0">
                      <h2
                        className="text-xl font-semibold text-foreground"
                        style={{ fontFamily: "'Playfair Display', serif" }}
                      >
                        {req.title}
                      </h2>
                      <p className="text-sm text-muted-foreground mt-1">
                        {req.author} · Soumis le {req.submitted}
                      </p>
                    </div>
                    <StatusBadge status={req.status} />
                  </div>
                </div>
                <div className="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                  <div className="grid grid-cols-2 gap-4">
                    {[
                      ["Catégorie", "Sciences naturelles"],
                      ["Année", "2023"],
                      ["Éditeur", "UNSTIM Éditions"],
                      ["Format", "PDF · 4,2 Mo"],
                    ].map(([k, v]) => (
                      <div key={k}>
                        <p className="text-[11px] text-muted-foreground uppercase tracking-wide font-semibold mb-0.5">
                          {k}
                        </p>
                        <p className="text-sm text-foreground">{v}</p>
                      </div>
                    ))}
                  </div>

                  <div>
                    <p className="text-[11px] text-muted-foreground uppercase tracking-wide font-semibold mb-2">
                      Résumé
                    </p>
                    <p className="text-sm text-foreground leading-relaxed">
                      Ouvrage académique traitant des fondements de la biologie cellulaire dans le contexte ouest-africain, avec des études de cas pratiques et des exercices corrigés destinés aux étudiants de licence et master.
                    </p>
                  </div>

                  <div className="flex items-center gap-3">
                    <button className="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm border border-border hover:bg-muted transition-colors">
                      <Eye size={14} />
                      Prévisualiser
                    </button>
                    <button className="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm border border-border hover:bg-muted transition-colors">
                      <Download size={14} />
                      Télécharger
                    </button>
                  </div>

                  {req.status === "pending" || req.status === "second_opinion" ? (
                    <div className="border-t border-border pt-5">
                      <p className="text-sm font-semibold text-foreground mb-3">Votre décision</p>
                      <div className="flex gap-3 mb-4">
                        <button
                          onClick={() => setDecision("approve")}
                          className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-medium border-2 transition-all ${
                            decision === "approve"
                              ? "border-emerald-500 bg-emerald-50 text-emerald-700"
                              : "border-border hover:border-emerald-300 text-muted-foreground"
                          }`}
                        >
                          <CheckCircle size={15} />
                          Valider
                        </button>
                        <button
                          onClick={() => setDecision("reject")}
                          className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-medium border-2 transition-all ${
                            decision === "reject"
                              ? "border-red-500 bg-red-50 text-red-700"
                              : "border-border hover:border-red-300 text-muted-foreground"
                          }`}
                        >
                          <XCircle size={15} />
                          Refuser
                        </button>
                      </div>
                      {decision && (
                        <div className="space-y-3">
                          <div>
                            <label className="text-sm font-medium text-foreground mb-1.5 block">
                              {decision === "reject"
                                ? "Justification du refus *"
                                : "Commentaire pour l'administrateur (optionnel)"}
                            </label>
                            <textarea
                              rows={3}
                              value={justification}
                              onChange={(e) => setJustification(e.target.value)}
                              placeholder={
                                decision === "reject"
                                  ? "Expliquez précisément les raisons du refus — cette justification sera transmise au déposant et à l'administrateur..."
                                  : "Ajoutez une observation complémentaire..."
                              }
                              className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background resize-none placeholder:text-muted-foreground"
                            />
                          </div>
                          <button
                            className={`w-full py-2.5 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90 ${
                              decision === "approve" ? "bg-emerald-600" : "bg-red-600"
                            }`}
                          >
                            {decision === "approve"
                              ? "Confirmer et transmettre à l'administrateur"
                              : "Confirmer le refus"}
                          </button>
                        </div>
                      )}
                    </div>
                  ) : (
                    req.justification && (
                      <div className="border-t border-border pt-5">
                        <p className="text-[11px] text-muted-foreground uppercase tracking-wide font-semibold mb-2">
                          Décision enregistrée
                        </p>
                        <div
                          className={`p-3 rounded-lg text-sm ${
                            req.status === "rejected"
                              ? "bg-red-50 border border-red-100 text-red-700"
                              : "bg-emerald-50 border border-emerald-100 text-emerald-700"
                          }`}
                        >
                          {req.justification}
                        </div>
                      </div>
                    )
                  )}
                </div>
              </>
            ) : (
              <div className="flex-1 flex items-center justify-center text-muted-foreground text-sm">
                Sélectionnez une demande
              </div>
            )}
          </div>
        </div>
      </div>
    );
  }

  // Default manager dashboard
  return (
    <div>
      <div className="mb-7">
        <h1
          className="text-2xl font-bold text-foreground"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          Tableau de bord
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Responsable chargé de la gestion des demandes
        </p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard icon={Clock} label="En attente d'examen" value={8} bg="bg-amber-500" />
        <StatCard icon={CheckCircle} label="Validées ce mois" value={14} trend="+4 vs mois dernier" bg="bg-[#1B4332]" />
        <StatCard icon={XCircle} label="Refusées ce mois" value={3} bg="bg-red-500" />
        <StatCard icon={RefreshCw} label="Seconds avis en cours" value={2} bg="bg-purple-600" />
      </div>

      <div className="grid grid-cols-3 gap-6">
        <div className="col-span-2 bg-card rounded-xl border border-border">
          <div className="px-5 py-4 border-b border-border">
            <h2
              className="font-semibold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              Demandes récentes assignées
            </h2>
          </div>
          {depositRequests.map((r) => (
            <div
              key={r.id}
              className="px-5 py-4 border-b border-border last:border-0 flex items-center justify-between gap-4 hover:bg-muted/20 transition-colors"
            >
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-foreground truncate">{r.title}</p>
                <p className="text-xs text-muted-foreground mt-0.5">
                  {r.author} · {r.submitted}
                </p>
              </div>
              <div className="flex items-center gap-3 flex-shrink-0">
                <StatusBadge status={r.status} />
                <button className="text-xs text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                  Examiner <ChevronRight size={13} />
                </button>
              </div>
            </div>
          ))}
        </div>

        <div className="bg-card rounded-xl border border-border p-5">
          <h3
            className="font-semibold text-foreground mb-4"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Mes statistiques
          </h3>
          <div className="space-y-4">
            {[
              { label: "Validées", value: 47, total: 60, color: "#1B4332" },
              { label: "Refusées", value: 13, total: 60, color: "#C17D0E" },
            ].map((s) => (
              <div key={s.label}>
                <div className="flex items-center justify-between mb-1.5">
                  <span className="text-sm text-foreground">{s.label}</span>
                  <span
                    className="text-sm font-semibold text-foreground"
                    style={{ fontFamily: "'Playfair Display', serif" }}
                  >
                    {s.value}
                  </span>
                </div>
                <div className="h-2 bg-muted rounded-full overflow-hidden">
                  <div
                    className="h-full rounded-full transition-all"
                    style={{ width: `${(s.value / s.total) * 100}%`, backgroundColor: s.color }}
                  />
                </div>
              </div>
            ))}
          </div>
          <div className="mt-5 pt-5 border-t border-border">
            <p className="text-xs text-muted-foreground mb-3">Délai moyen de traitement</p>
            <p
              className="text-3xl font-bold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              2,4{" "}
              <span className="text-base font-normal text-muted-foreground">jours</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── ADMIN VIEWS ──────────────────────────────────────────────────────────────

function AdminDashboard({ nav }: { nav: string }) {
  const [overrideId, setOverrideId] = useState<number | null>(null);
  const [overrideJust, setOverrideJust] = useState("");
  const [reqFilter, setReqFilter] = useState("Toutes");

  if (nav === "requests") {
    const filters = ["Toutes", "En attente", "Validées", "Refusées", "Publiées", "Second avis"];
    const filterMap: Record<string, string> = {
      "En attente": "pending",
      Validées: "approved",
      Refusées: "rejected",
      Publiées: "published",
      "Second avis": "second_opinion",
    };
    const filtered =
      reqFilter === "Toutes"
        ? depositRequests
        : depositRequests.filter((r) => r.status === filterMap[reqFilter]);

    return (
      <div>
        <div className="mb-6">
          <h1
            className="text-2xl font-bold text-foreground"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Circuit de validation
          </h1>
          <p className="text-muted-foreground mt-1 text-sm">
            Supervision de toutes les demandes de dépôt
          </p>
        </div>

        <div className="bg-card rounded-xl border border-border overflow-hidden">
          <div className="px-5 py-4 border-b border-border flex items-center gap-2 flex-wrap">
            {filters.map((f) => (
              <button
                key={f}
                onClick={() => setReqFilter(f)}
                className={`px-3 py-1.5 text-xs rounded-full font-medium transition-all ${
                  reqFilter === f
                    ? "text-white"
                    : "text-muted-foreground hover:text-foreground hover:bg-muted"
                }`}
                style={reqFilter === f ? { backgroundColor: "#1B4332" } : {}}
              >
                {f}
              </button>
            ))}
          </div>
          <table className="w-full">
            <thead>
              <tr
                className="border-b border-border"
                style={{ backgroundColor: "rgba(235,228,212,0.4)" }}
              >
                {["Titre", "Auteur", "Soumis le", "Responsable", "Statut", "Actions"].map((h) => (
                  <th
                    key={h}
                    className="px-5 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-wider"
                  >
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {filtered.map((r) => (
                <tr key={r.id} className="hover:bg-muted/20 transition-colors">
                  <td className="px-5 py-3.5 max-w-xs">
                    <p className="text-sm font-medium text-foreground truncate">{r.title}</p>
                  </td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground whitespace-nowrap">{r.author}</td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground whitespace-nowrap">{r.submitted}</td>
                  <td className="px-5 py-3.5 text-sm text-foreground">{r.manager ?? "—"}</td>
                  <td className="px-5 py-3.5">
                    <StatusBadge status={r.status} />
                  </td>
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-1.5">
                      {r.status === "rejected" && (
                        <button
                          onClick={() => setOverrideId(r.id)}
                          className="px-2.5 py-1 text-xs rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors font-medium"
                        >
                          Invalider le refus
                        </button>
                      )}
                      {r.status === "approved" && (
                        <button className="px-2.5 py-1 text-xs rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors font-medium">
                          Publier
                        </button>
                      )}
                      {r.status === "pending" && (
                        <button className="px-2.5 py-1 text-xs rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50 transition-colors font-medium">
                          Second avis
                        </button>
                      )}
                      <button className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors">
                        <Eye size={14} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Override modal */}
        {overrideId !== null && (
          <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div className="bg-card rounded-2xl border border-border p-7 max-w-md w-full shadow-2xl">
              <div className="flex items-start gap-3 mb-4">
                <div className="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                  <AlertCircle size={20} className="text-amber-600" />
                </div>
                <div>
                  <h3
                    className="text-lg font-semibold text-foreground"
                    style={{ fontFamily: "'Playfair Display', serif" }}
                  >
                    Invalider le refus
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1">
                    Vous passez outre la décision du responsable. La référence sera publiée et le responsable notifié avec votre justification.
                  </p>
                </div>
              </div>
              <div className="mb-5">
                <label className="text-sm font-medium text-foreground mb-1.5 block">
                  Justification de votre décision *
                </label>
                <textarea
                  rows={4}
                  value={overrideJust}
                  onChange={(e) => setOverrideJust(e.target.value)}
                  placeholder="Expliquez les raisons pour lesquelles vous estimez que le refus n'est pas fondé..."
                  className="w-full px-3 py-2.5 rounded-lg border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-input-background resize-none placeholder:text-muted-foreground"
                />
              </div>
              <div className="flex gap-3">
                <button
                  className="flex-1 py-2.5 text-white rounded-xl text-sm font-semibold transition-opacity hover:opacity-90"
                  style={{ backgroundColor: "#1B4332" }}
                >
                  Confirmer et publier
                </button>
                <button
                  onClick={() => setOverrideId(null)}
                  className="px-4 py-2.5 rounded-xl text-sm border border-border hover:bg-muted transition-colors"
                >
                  Annuler
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    );
  }

  // Default admin dashboard
  return (
    <div>
      <div className="mb-7">
        <h1
          className="text-2xl font-bold text-foreground"
          style={{ fontFamily: "'Playfair Display', serif" }}
        >
          Tableau de bord
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">Vue d'ensemble de la plateforme</p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard icon={BookOpen} label="Références publiées" value={1247} trend="+34 ce mois" bg="bg-[#1B4332]" />
        <StatCard icon={Users} label="Utilisateurs inscrits" value="4 892" trend="+127 ce mois" bg="bg-blue-600" />
        <StatCard icon={Clock} label="Demandes en cours" value={18} bg="bg-amber-500" />
        <StatCard icon={Eye} label="Consultations (30j)" value="28 400" trend="+12%" bg="bg-purple-600" />
      </div>

      <div className="grid grid-cols-3 gap-6 mb-6">
        <div className="col-span-2 bg-card rounded-xl border border-border p-5">
          <div className="flex items-center justify-between mb-4">
            <h3
              className="font-semibold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              Activité des demandes
            </h3>
            <select className="text-xs border border-border rounded-lg px-2 py-1 bg-transparent text-muted-foreground outline-none">
              <option>7 derniers mois</option>
              <option>12 derniers mois</option>
            </select>
          </div>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={statsData} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="rgba(28,25,22,0.06)" />
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6E6249" }} />
              <YAxis tick={{ fontSize: 11, fill: "#6E6249" }} />
              <Tooltip
                contentStyle={{
                  backgroundColor: "#FDFAF3",
                  border: "1px solid rgba(28,25,22,0.1)",
                  borderRadius: 8,
                  fontSize: 12,
                }}
              />
              <Bar dataKey="publiées" name="Publiées" fill="#1B4332" radius={[3, 3, 0, 0]} />
              <Bar dataKey="refusées" name="Refusées" fill="#C17D0E" radius={[3, 3, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-xl border border-border p-5">
          <h3
            className="font-semibold text-foreground mb-3"
            style={{ fontFamily: "'Playfair Display', serif" }}
          >
            Par catégorie
          </h3>
          <ResponsiveContainer width="100%" height={150}>
            <PieChart>
              <Pie
                data={pieData}
                cx="50%"
                cy="50%"
                innerRadius={42}
                outerRadius={65}
                dataKey="value"
                paddingAngle={2}
              >
                {pieData.map((entry, index) => (
                  <Cell key={index} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip
                contentStyle={{
                  backgroundColor: "#FDFAF3",
                  border: "1px solid rgba(28,25,22,0.1)",
                  borderRadius: 8,
                  fontSize: 12,
                }}
                formatter={(v) => [`${v} références`]}
              />
            </PieChart>
          </ResponsiveContainer>
          <div className="space-y-1.5 mt-2">
            {pieData.slice(0, 5).map((d) => (
              <div key={d.name} className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-2.5 h-2.5 rounded-sm flex-shrink-0" style={{ backgroundColor: d.color }} />
                  <span className="text-xs text-muted-foreground">{d.name}</span>
                </div>
                <span
                  className="text-xs font-semibold text-foreground"
                  style={{ fontFamily: "'DM Mono', monospace" }}
                >
                  {d.value}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-6">
        <div className="bg-card rounded-xl border border-border">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2
              className="font-semibold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              Décisions en attente
            </h2>
            <span className="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-medium">
              5 urgentes
            </span>
          </div>
          {depositRequests
            .filter((r) => ["pending", "rejected", "second_opinion"].includes(r.status))
            .map((r) => (
              <div
                key={r.id}
                className="px-5 py-3.5 border-b border-border last:border-0 flex items-center justify-between gap-3 hover:bg-muted/20 transition-colors"
              >
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-foreground truncate">{r.title}</p>
                  <p className="text-xs text-muted-foreground">{r.author} · {r.submitted}</p>
                </div>
                <div className="flex items-center gap-2 flex-shrink-0">
                  <StatusBadge status={r.status} />
                  <button className="text-muted-foreground hover:text-foreground transition-colors">
                    <ChevronRight size={15} />
                  </button>
                </div>
              </div>
            ))}
        </div>

        <div className="bg-card rounded-xl border border-border">
          <div className="px-5 py-4 border-b border-border">
            <h2
              className="font-semibold text-foreground"
              style={{ fontFamily: "'Playfair Display', serif" }}
            >
              Journal d'activité récent
            </h2>
          </div>
          <div className="divide-y divide-border">
            {[
              { msg: "Référence publiée : « Droit commercial et affaires internationales »", time: "Il y a 1h", icon: CheckCircle, color: "text-emerald-500" },
              { msg: "Refus invalidé par l'admin pour « Biologie cellulaire avancée »", time: "Il y a 3h", icon: RefreshCw, color: "text-amber-600" },
              { msg: "Second avis demandé : « Gouvernance locale »", time: "Il y a 5h", icon: MessageSquare, color: "text-purple-500" },
              { msg: "Nouvel utilisateur inscrit : T. Mensah", time: "Il y a 6h", icon: Users, color: "text-blue-500" },
              { msg: "Compte désactivé : A. Diallo", time: "Hier 14h22", icon: UserX, color: "text-red-500" },
            ].map((item, i) => (
              <div
                key={i}
                className="px-5 py-3.5 flex items-start gap-3 hover:bg-muted/20 transition-colors"
              >
                <item.icon size={14} className={`mt-0.5 flex-shrink-0 ${item.color}`} />
                <div className="flex-1 min-w-0">
                  <p className="text-xs text-foreground leading-snug">{item.msg}</p>
                  <p className="text-[11px] text-muted-foreground mt-0.5">{item.time}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── MAIN APP ─────────────────────────────────────────────────────────────────

export default function App() {
  const [activeRole, setActiveRole] = useState<Role>("visitor");
  const [userNav, setUserNav] = useState("dashboard");
  const [hrNav, setHrNav] = useState("dashboard");
  const [managerNav, setManagerNav] = useState("dashboard");
  const [adminNav, setAdminNav] = useState("dashboard");

  const roles: { id: Role; label: string; icon: React.ElementType }[] = [
    { id: "visitor", label: "Visiteur", icon: Eye },
    { id: "user", label: "Utilisateur inscrit", icon: User },
    { id: "hr", label: "Responsable RH", icon: Users },
    { id: "manager", label: "Resp. Demandes", icon: FileText },
    { id: "admin", label: "Administrateur", icon: Shield },
  ];

  const userNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Tableau de bord", icon: Home },
    { id: "catalog", label: "Catalogue", icon: BookOpen },
    { id: "requests", label: "Mes demandes", icon: FileText, badge: 2 },
    { id: "submit", label: "Soumettre une référence", icon: Upload },
    { id: "profile", label: "Mon profil", icon: User },
  ];

  const hrNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Tableau de bord", icon: Home },
    { id: "users", label: "Gestion utilisateurs", icon: Users },
    { id: "logs", label: "Journal d'activités", icon: Activity },
  ];

  const managerNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Tableau de bord", icon: Home },
    { id: "requests", label: "Demandes assignées", icon: FileText, badge: 8 },
    { id: "second", label: "Seconds avis", icon: RefreshCw, badge: 2 },
  ];

  const adminNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Tableau de bord", icon: Home },
    { id: "requests", label: "Toutes les demandes", icon: FileText, badge: 5 },
    { id: "references", label: "Références publiées", icon: BookOpen },
    { id: "catalog", label: "Données catalogue", icon: Archive },
    { id: "users", label: "Utilisateurs & rôles", icon: Users },
    { id: "logs", label: "Journaux d'activité", icon: Activity },
    { id: "settings", label: "Paramètres", icon: Settings },
  ];

  return (
    <div
      className="min-h-screen bg-background"
      style={{ fontFamily: "'DM Sans', sans-serif" }}
    >
      {/* Preview toolbar */}
      <div
        className="h-12 flex items-center px-5 gap-1 overflow-x-auto flex-shrink-0"
        style={{ backgroundColor: "#0F2419" }}
      >
        <span className="text-[10px] text-white/30 font-semibold tracking-[0.18em] uppercase mr-3 whitespace-nowrap flex-shrink-0">
          Prévisualisation
        </span>
        <div className="h-4 w-px bg-white/10 mr-2 flex-shrink-0" />
        {roles.map((role) => (
          <button
            key={role.id}
            onClick={() => setActiveRole(role.id)}
            className={`flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium whitespace-nowrap transition-all rounded-lg flex-shrink-0 ${
              activeRole === role.id
                ? "bg-white/15 text-white"
                : "text-white/40 hover:text-white/70"
            }`}
          >
            <role.icon size={12} />
            {role.label}
          </button>
        ))}
      </div>

      {/* Role content */}
      {activeRole === "visitor" && <VisitorPage />}

      {activeRole === "user" && (
        <AppShell
          sidebarItems={userNavItems}
          activeNav={userNav}
          onNavigate={setUserNav}
          userName="Fatou Camara"
          roleLabel="Utilisateur inscrit"
        >
          <UserDashboard nav={userNav} setNav={setUserNav} />
        </AppShell>
      )}

      {activeRole === "hr" && (
        <AppShell
          sidebarItems={hrNavItems}
          activeNav={hrNav}
          onNavigate={setHrNav}
          userName="Marie Agbodossou"
          roleLabel="Responsable RH"
        >
          <HRDashboard nav={hrNav} />
        </AppShell>
      )}

      {activeRole === "manager" && (
        <AppShell
          sidebarItems={managerNavItems}
          activeNav={managerNav}
          onNavigate={setManagerNav}
          userName="Pierre Houénou"
          roleLabel="Resp. Demandes"
        >
          <ManagerDashboard nav={managerNav} />
        </AppShell>
      )}

      {activeRole === "admin" && (
        <AppShell
          sidebarItems={adminNavItems}
          activeNav={adminNav}
          onNavigate={setAdminNav}
          userName="Admin Principal"
          roleLabel="Administrateur"
        >
          <AdminDashboard nav={adminNav} />
        </AppShell>
      )}
    </div>
  );
}
