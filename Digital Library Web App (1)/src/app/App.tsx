import { useState, useEffect, useRef } from "react";
import {
  Search, BookOpen, Download, Eye, Bell, ChevronRight,
  Users, FileText, CheckCircle, XCircle, Clock, AlertCircle,
  Settings, LogOut, Plus, Filter, Shield, UserCheck, UserX,
  TrendingUp, Library, Home, Upload, MessageSquare, RefreshCw,
  Edit2, Trash2, Archive, Activity, User, ChevronDown, Menu, X,
  MoreHorizontal, ArrowLeft,
} from "lucide-react";
import {
  AreaChart, Area, BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, PieChart, Pie, Cell,
} from "recharts";

// ─── Types ────────────────────────────────────────────────────────────────────

type Role = "visitor" | "user" | "hr" | "manager" | "admin";

interface NavItem {
  id: string;
  label: string;
  icon: React.ElementType;
  badge?: number;
}
type SidebarItem = NavItem;

// ─── Mock Data ────────────────────────────────────────────────────────────────

const catalogBooks = [
  { id: 1, title: "Introduction au droit administratif", author: "Jean-Claude Bonardi", year: 2022, category: "Droit", cover: "https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=300&h=420&fit=crop&auto=format", views: 842, downloads: 213 },
  { id: 2, title: "Économie politique africaine", author: "Amina Touré", year: 2021, category: "Économie", cover: "https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=300&h=420&fit=crop&auto=format", views: 1204, downloads: 387 },
  { id: 3, title: "Histoire du Bénin précolonial", author: "Didier Aplogan", year: 2020, category: "Histoire", cover: "https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=300&h=420&fit=crop&auto=format", views: 976, downloads: 294 },
  { id: 4, title: "Mathématiques pour ingénieurs", author: "Paul Segla", year: 2023, category: "Sciences", cover: "https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=300&h=420&fit=crop&auto=format", views: 531, downloads: 178 },
  { id: 5, title: "Littérature francophone contemporaine", author: "Marie-Claire Ahoho", year: 2022, category: "Littérature", cover: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=300&h=420&fit=crop&auto=format", views: 688, downloads: 201 },
  { id: 6, title: "Santé publique et épidémiologie", author: "Rodrigue Gbédji", year: 2023, category: "Médecine", cover: "https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=300&h=420&fit=crop&auto=format", views: 445, downloads: 132 },
  { id: 7, title: "Philosophie africaine moderne", author: "Célestin Hounsou", year: 2021, category: "Philosophie", cover: "https://images.unsplash.com/photo-1512820790803-83ca734da794?w=300&h=420&fit=crop&auto=format", views: 320, downloads: 98 },
  { id: 8, title: "Agriculture durable au Sahel", author: "Koffi Mensah", year: 2022, category: "Sciences", cover: "https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=300&h=420&fit=crop&auto=format", views: 567, downloads: 214 },
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
  { name: "Médecine", value: 12, color: "#34C759" },
  { name: "Littérature", value: 10, color: "#FF9F0A" },
  { name: "Autre", value: 21, color: "#C7C7CC" },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; bg: string; text: string; dot: string }> = {
  pending:       { label: "En attente",  bg: "bg-amber-50",   text: "text-amber-700",  dot: "bg-amber-400" },
  approved:      { label: "Validée",     bg: "bg-green-50",   text: "text-green-700",  dot: "bg-green-500" },
  rejected:      { label: "Refusée",     bg: "bg-red-50",     text: "text-red-600",    dot: "bg-red-500"   },
  published:     { label: "Publiée",     bg: "bg-blue-50",    text: "text-blue-700",   dot: "bg-blue-500"  },
  second_opinion:{ label: "Second avis", bg: "bg-purple-50",  text: "text-purple-700", dot: "bg-purple-500"},
  active:        { label: "Actif",       bg: "bg-green-50",   text: "text-green-700",  dot: "bg-green-500" },
  inactive:      { label: "Inactif",     bg: "bg-gray-100",   text: "text-gray-500",   dot: "bg-gray-400"  },
};

function StatusBadge({ status }: { status: string }) {
  const c = statusConfig[status] ?? statusConfig.pending;
  return (
    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${c.bg} ${c.text}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${c.dot}`} />
      {c.label}
    </span>
  );
}

function Avatar({ name, size = "md" }: { name: string; size?: "sm" | "md" | "lg" }) {
  const initials = name.split(" ").map(n => n[0]).join("").slice(0, 2).toUpperCase();
  const sz = size === "sm" ? "w-7 h-7 text-xs" : size === "lg" ? "w-11 h-11 text-base" : "w-9 h-9 text-sm";
  return (
    <div className={`${sz} rounded-full flex items-center justify-center font-semibold text-white flex-shrink-0`}
      style={{ background: "linear-gradient(135deg, #1B4332 0%, #40916C 100%)" }}>
      {initials}
    </div>
  );
}

function StatCard({ icon: Icon, label, value, trend, color }: {
  icon: React.ElementType; label: string; value: string | number; trend?: string; color: string;
}) {
  return (
    <div className="bg-white rounded-2xl p-5 shadow-sm border border-border">
      <div className="flex items-start justify-between">
        <div>
          <p className="text-xs font-medium text-muted-foreground uppercase tracking-wide">{label}</p>
          <p className="text-2xl font-bold text-foreground mt-1.5" style={{ fontFamily: "'Playfair Display', serif" }}>
            {value}
          </p>
          {trend && (
            <p className="text-xs text-green-600 font-medium mt-1.5 flex items-center gap-1">
              <TrendingUp size={11} />{trend}
            </p>
          )}
        </div>
        <div className={`w-11 h-11 rounded-2xl flex items-center justify-center ${color}`}>
          <Icon size={19} className="text-white" />
        </div>
      </div>
    </div>
  );
}

// ─── Section header ───────────────────────────────────────────────────────────

function SectionTitle({ children, action }: { children: React.ReactNode; action?: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between mb-4">
      <h2 className="text-base font-semibold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
        {children}
      </h2>
      {action}
    </div>
  );
}

// ─── Drawer (mobile nav) ──────────────────────────────────────────────────────

function MobileDrawer({
  open, onClose, items, active, onNavigate, userName, roleLabel,
}: {
  open: boolean; onClose: () => void;
  items: NavItem[]; active: string; onNavigate: (id: string) => void;
  userName: string; roleLabel: string;
}) {
  useEffect(() => {
    if (open) document.body.style.overflow = "hidden";
    else document.body.style.overflow = "";
    return () => { document.body.style.overflow = ""; };
  }, [open]);

  return (
    <>
      {/* Backdrop */}
      <div
        className={`fixed inset-0 z-40 transition-opacity duration-300 ${open ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        style={{ backgroundColor: "rgba(0,0,0,0.4)", backdropFilter: "blur(4px)" }}
        onClick={onClose}
      />
      {/* Drawer */}
      <div
        className={`fixed inset-y-0 left-0 z-50 w-72 flex flex-col transition-transform duration-300 ease-out ${open ? "translate-x-0" : "-translate-x-full"}`}
        style={{ backgroundColor: "#1B4332" }}
      >
        {/* Header */}
        <div className="flex items-center justify-between px-5 pt-12 pb-6 border-b border-white/10">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
              <Library size={17} className="text-white" />
            </div>
            <div>
              <p className="text-white font-semibold text-base" style={{ fontFamily: "'Playfair Display', serif" }}>BibliNum</p>
              <p className="text-white/40 text-xs">Bibliothèque Nationale</p>
            </div>
          </div>
          <button onClick={onClose} className="text-white/50 hover:text-white transition-colors p-1">
            <X size={20} />
          </button>
        </div>

        {/* Nav items */}
        <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
          {items.map(item => (
            <button
              key={item.id}
              onClick={() => { onNavigate(item.id); onClose(); }}
              className={`w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm transition-all text-left ${
                active === item.id ? "bg-white/20 text-white font-medium" : "text-white/60 hover:text-white hover:bg-white/10"
              }`}
            >
              <item.icon size={18} />
              <span className="flex-1">{item.label}</span>
              {item.badge !== undefined && item.badge > 0 && (
                <span className="bg-amber-500 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[20px] text-center py-0.5">
                  {item.badge}
                </span>
              )}
            </button>
          ))}
        </nav>

        {/* User */}
        <div className="px-4 py-6 border-t border-white/10">
          <div className="flex items-center gap-3">
            <Avatar name={userName} size="sm" />
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">{userName}</p>
              <p className="text-white/40 text-xs truncate">{roleLabel}</p>
            </div>
            <button className="text-white/30 hover:text-white/70 transition-colors">
              <LogOut size={16} />
            </button>
          </div>
        </div>
      </div>
    </>
  );
}

// ─── Top Navbar ───────────────────────────────────────────────────────────────

function TopNav({
  items, active, onNavigate, userName, roleLabel,
}: {
  items: NavItem[]; active: string; onNavigate: (id: string) => void;
  userName: string; roleLabel: string;
}) {
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) setUserMenuOpen(false);
    }
    document.addEventListener("mousedown", handleClick);
    return () => document.removeEventListener("mousedown", handleClick);
  }, []);

  return (
    <>
      <MobileDrawer
        open={drawerOpen} onClose={() => setDrawerOpen(false)}
        items={items} active={active} onNavigate={onNavigate}
        userName={userName} roleLabel={roleLabel}
      />

      <header
        className="sticky top-0 z-30 flex-shrink-0"
        style={{ backgroundColor: "#1B4332", backdropFilter: "blur(20px)" }}
      >
        <div className="flex items-center h-14 px-4 sm:px-6 gap-3">
          {/* Hamburger (mobile) */}
          <button
            className="md:hidden p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors"
            onClick={() => setDrawerOpen(true)}
          >
            <Menu size={20} />
          </button>

          {/* Logo */}
          <div className="flex items-center gap-2.5 flex-shrink-0">
            <div className="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center hidden md:flex">
              <Library size={14} className="text-white" />
            </div>
            <span className="text-white font-semibold text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>
              BibliNum
            </span>
          </div>

          <div className="hidden md:block h-5 w-px bg-white/20 mx-1" />

          {/* Nav links (desktop) */}
          <nav className="hidden md:flex items-center gap-0.5 flex-1 overflow-x-auto">
            {items.map(item => (
              <button
                key={item.id}
                onClick={() => onNavigate(item.id)}
                className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm whitespace-nowrap transition-all flex-shrink-0 ${
                  active === item.id
                    ? "bg-white/20 text-white font-medium"
                    : "text-white/55 hover:text-white hover:bg-white/10"
                }`}
              >
                <item.icon size={15} />
                {item.label}
                {item.badge !== undefined && item.badge > 0 && (
                  <span className="bg-amber-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center leading-none">
                    {item.badge}
                  </span>
                )}
              </button>
            ))}
          </nav>

          {/* Spacer on mobile */}
          <div className="flex-1 md:hidden" />

          {/* Right actions */}
          <div className="flex items-center gap-1">
            <button
              onClick={() => setSearchOpen(v => !v)}
              className={`p-2 rounded-xl transition-colors ${searchOpen ? "bg-white/20 text-white" : "text-white/60 hover:text-white hover:bg-white/10"}`}
            >
              <Search size={17} />
            </button>
            <button className="relative p-2 text-white/60 hover:text-white hover:bg-white/10 rounded-xl transition-colors">
              <Bell size={17} />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-amber-400 rounded-full border-2 border-[#1B4332]" />
            </button>

            <div className="hidden md:block h-5 w-px bg-white/20 mx-1" />

            {/* User chip */}
            <div className="relative" ref={menuRef}>
              <button
                onClick={() => setUserMenuOpen(v => !v)}
                className="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl hover:bg-white/10 transition-colors"
              >
                <Avatar name={userName} size="sm" />
                <span className="hidden lg:block text-white text-xs font-medium">{userName.split(" ")[0]}</span>
                <ChevronDown size={13} className="hidden md:block text-white/40" />
              </button>

              {userMenuOpen && (
                <div className="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-border overflow-hidden z-50">
                  <div className="px-4 py-3.5 border-b border-border">
                    <p className="text-sm font-semibold text-foreground">{userName}</p>
                    <p className="text-xs text-muted-foreground mt-0.5">{roleLabel}</p>
                  </div>
                  {[{ icon: User, label: "Mon profil" }, { icon: Settings, label: "Paramètres" }].map(item => (
                    <button key={item.label}
                      className="w-full flex items-center gap-3 px-4 py-3 text-sm text-foreground hover:bg-muted transition-colors">
                      <item.icon size={15} className="text-muted-foreground" />
                      {item.label}
                    </button>
                  ))}
                  <div className="border-t border-border">
                    <button className="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                      <LogOut size={15} />Se déconnecter
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Expandable search */}
        {searchOpen && (
          <div className="border-t border-white/10 px-4 sm:px-6 py-3" style={{ backgroundColor: "rgba(10,28,18,0.5)" }}>
            <div className="max-w-2xl flex items-center gap-3 bg-white/10 border border-white/15 rounded-2xl px-4 py-2.5">
              <Search size={15} className="text-white/50 flex-shrink-0" />
              <input
                autoFocus
                placeholder="Titre, auteur, catégorie, mots-clés..."
                className="flex-1 bg-transparent outline-none text-white text-sm placeholder:text-white/40"
                onKeyDown={e => e.key === "Escape" && setSearchOpen(false)}
              />
              <button onClick={() => setSearchOpen(false)} className="text-white/30 hover:text-white/60">
                <X size={15} />
              </button>
            </div>
          </div>
        )}
      </header>
    </>
  );
}

// ─── Bottom Tab Bar (mobile only) ─────────────────────────────────────────────

function BottomTabBar({ items, active, onNavigate }: {
  items: NavItem[]; active: string; onNavigate: (id: string) => void;
}) {
  const tabs = items.slice(0, 5);
  return (
    <nav
      className="md:hidden fixed bottom-0 left-0 right-0 z-30 flex items-center border-t border-border"
      style={{
        backgroundColor: "rgba(255,255,255,0.92)",
        backdropFilter: "blur(20px)",
        paddingBottom: "env(safe-area-inset-bottom, 0px)",
      }}
    >
      {tabs.map(item => {
        const isActive = active === item.id;
        return (
          <button
            key={item.id}
            onClick={() => onNavigate(item.id)}
            className="flex-1 flex flex-col items-center justify-center py-2 gap-1 transition-all relative"
          >
            <div className="relative">
              <item.icon
                size={22}
                className="transition-all"
                style={{ color: isActive ? "#1B4332" : "#6E6E73", strokeWidth: isActive ? 2.2 : 1.7 }}
              />
              {item.badge !== undefined && item.badge > 0 && (
                <span className="absolute -top-1 -right-1.5 bg-red-500 text-white text-[9px] font-bold rounded-full px-1 min-w-[16px] text-center leading-4">
                  {item.badge}
                </span>
              )}
            </div>
            <span
              className="text-[10px] font-medium transition-all leading-none"
              style={{ color: isActive ? "#1B4332" : "#6E6E73" }}
            >
              {item.label.split(" ")[0]}
            </span>
            {isActive && (
              <span className="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 rounded-full" style={{ backgroundColor: "#1B4332" }} />
            )}
          </button>
        );
      })}
    </nav>
  );
}

// ─── App Shell ────────────────────────────────────────────────────────────────

function AppShell({ sidebarItems, activeNav, onNavigate, userName, roleLabel, children }: {
  sidebarItems: SidebarItem[]; activeNav: string; onNavigate: (id: string) => void;
  userName: string; roleLabel: string; children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col" style={{ minHeight: "calc(100vh - 48px)", backgroundColor: "#F2F2F7" }}>
      <TopNav items={sidebarItems} active={activeNav} onNavigate={onNavigate} userName={userName} roleLabel={roleLabel} />
      <main className="flex-1 overflow-auto pb-24 md:pb-8 px-4 sm:px-6 py-5">
        {children}
      </main>
      <BottomTabBar items={sidebarItems} active={activeNav} onNavigate={onNavigate} />
    </div>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// VISITOR PAGE
// ═══════════════════════════════════════════════════════════════════════════════

function VisitorPage() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("Tout");
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const categories = ["Tout", "Droit", "Économie", "Histoire", "Sciences", "Littérature", "Médecine"];

  const filtered = catalogBooks.filter(b =>
    (category === "Tout" || b.category === category) &&
    (b.title.toLowerCase().includes(query.toLowerCase()) || b.author.toLowerCase().includes(query.toLowerCase()))
  );

  return (
    <div style={{ backgroundColor: "#F2F2F7", minHeight: "100vh" }}>
      {/* Public Navbar */}
      <nav
        className="sticky top-0 z-30 border-b border-border"
        style={{ backgroundColor: "rgba(255,255,255,0.85)", backdropFilter: "blur(20px)" }}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
          <div className="flex items-center gap-2.5">
            <Library size={20} style={{ color: "#1B4332" }} />
            <span className="text-base font-bold" style={{ fontFamily: "'Playfair Display', serif", color: "#1B4332" }}>
              BibliNum
            </span>
          </div>

          {/* Desktop nav links */}
          <div className="hidden md:flex items-center gap-6">
            <button className="text-sm text-muted-foreground hover:text-foreground transition-colors font-medium">Catalogue</button>
            <button className="text-sm text-muted-foreground hover:text-foreground transition-colors font-medium">Recherche avancée</button>
            <button className="text-sm text-muted-foreground hover:text-foreground transition-colors font-medium">À propos</button>
          </div>

          <div className="flex items-center gap-2">
            <button className="hidden sm:block px-4 py-2 text-sm rounded-xl border border-border hover:bg-muted transition-colors font-medium text-foreground">
              Connexion
            </button>
            <button className="px-4 py-2 text-sm rounded-xl text-white font-semibold transition-opacity hover:opacity-90" style={{ backgroundColor: "#1B4332" }}>
              S'inscrire
            </button>
            {/* Mobile hamburger */}
            <button className="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-xl hover:bg-muted transition-colors" onClick={() => setMobileMenuOpen(v => !v)}>
              {mobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
            </button>
          </div>
        </div>
        {/* Mobile menu */}
        {mobileMenuOpen && (
          <div className="md:hidden bg-white border-t border-border px-4 py-3 space-y-1">
            {["Catalogue", "Recherche avancée", "À propos", "Connexion"].map(l => (
              <button key={l} className="w-full text-left py-2.5 px-3 text-sm text-foreground hover:bg-muted rounded-xl transition-colors">
                {l}
              </button>
            ))}
          </div>
        )}
      </nav>

      {/* Hero */}
      <section
        className="relative overflow-hidden"
        style={{ background: "linear-gradient(150deg, #0F2419 0%, #1B4332 50%, #2D6A4F 100%)" }}
      >
        <div
          className="absolute inset-0 opacity-10"
          style={{
            backgroundImage: "url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1600&h=700&fit=crop&auto=format')",
            backgroundSize: "cover", backgroundPosition: "center",
          }}
        />
        <div className="relative max-w-5xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
          <div className="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-1.5 mb-6">
            <span className="w-2 h-2 bg-green-400 rounded-full" />
            <span className="text-white/80 text-xs font-medium">1 247 références disponibles</span>
          </div>
          <h1
            className="text-white mb-5 leading-[1.1] px-2"
            style={{ fontFamily: "'Playfair Display', serif", fontSize: "clamp(2rem, 6vw, 4rem)" }}
          >
            La connaissance,<br />accessible à tous
          </h1>
          <p className="text-white/65 mb-8 max-w-xl mx-auto leading-relaxed text-sm sm:text-base px-4">
            Explorez des milliers d'ouvrages académiques, thèses, manuels et articles scientifiques depuis n'importe quel appareil.
          </p>

          {/* Search */}
          <div className="flex items-center bg-white rounded-2xl shadow-2xl overflow-hidden max-w-2xl mx-auto">
            <Search size={17} className="ml-4 sm:ml-5 text-muted-foreground flex-shrink-0" />
            <input
              value={query}
              onChange={e => setQuery(e.target.value)}
              placeholder="Titre, auteur, mots-clés..."
              className="flex-1 px-3 sm:px-4 py-3.5 sm:py-4 text-sm sm:text-base outline-none bg-transparent text-foreground placeholder:text-muted-foreground"
            />
            <button
              className="px-4 sm:px-6 py-3.5 sm:py-4 text-white text-sm font-semibold flex-shrink-0 transition-opacity hover:opacity-90 m-1 rounded-xl"
              style={{ backgroundColor: "#1B4332" }}
            >
              Chercher
            </button>
          </div>

          {/* Key numbers */}
          <div className="flex items-center justify-center gap-8 sm:gap-16 mt-10">
            {[["1 247", "Références"], ["38", "Catégories"], ["12 800+", "Utilisateurs"]].map(([v, l]) => (
              <div key={l} className="text-center">
                <p className="text-white font-bold" style={{ fontFamily: "'Playfair Display', serif", fontSize: "clamp(1.5rem, 4vw, 2rem)" }}>{v}</p>
                <p className="text-white/45 text-xs sm:text-sm mt-0.5">{l}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Category pills */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-3">
        <div className="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
          {categories.map(cat => (
            <button
              key={cat}
              onClick={() => setCategory(cat)}
              className={`px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all flex-shrink-0 ${
                category === cat ? "text-white shadow-sm" : "bg-white border border-border text-muted-foreground hover:text-foreground"
              }`}
              style={category === cat ? { backgroundColor: "#1B4332" } : {}}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Catalog */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 pb-20">
        <div className="flex items-center justify-between mb-4">
          <p className="text-sm text-muted-foreground">
            <span className="font-semibold text-foreground">{filtered.length}</span> référence{filtered.length !== 1 ? "s" : ""}
          </p>
          <button className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors font-medium">
            <Filter size={14} />Filtres
          </button>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
          {filtered.map(book => (
            <div key={book.id} className="bg-white rounded-2xl overflow-hidden shadow-sm border border-border hover:shadow-md transition-all duration-200 group cursor-pointer">
              <div className="aspect-[3/4] bg-muted overflow-hidden">
                <img src={book.cover} alt={book.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
              </div>
              <div className="p-3">
                <span className="text-[10px] text-muted-foreground font-semibold uppercase tracking-wide">{book.category}</span>
                <h3 className="text-xs font-semibold text-foreground mt-1 leading-snug line-clamp-2">{book.title}</h3>
                <p className="text-[11px] text-muted-foreground mt-1 truncate">{book.author}</p>
                <div className="flex items-center gap-3 mt-2 text-[11px] text-muted-foreground">
                  <span className="flex items-center gap-0.5"><Eye size={11} />{book.views.toLocaleString("fr")}</span>
                  <span className="flex items-center gap-0.5"><Download size={11} />{book.downloads}</span>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* CTA */}
        <div className="mt-12 rounded-3xl p-8 sm:p-12 text-center" style={{ background: "linear-gradient(135deg, #0F2419 0%, #1B4332 100%)" }}>
          <Library size={32} className="mx-auto mb-4 text-white/60" />
          <h3 className="text-xl sm:text-2xl font-semibold text-white mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>
            Accédez à l'intégralité des ressources
          </h3>
          <p className="text-white/55 mb-6 max-w-md mx-auto text-sm leading-relaxed">
            Créez un compte gratuit pour lire en ligne, télécharger et soumettre vos propres références documentaires.
          </p>
          <button className="px-7 py-3 bg-white rounded-xl text-sm font-semibold hover:opacity-95 transition-opacity" style={{ color: "#1B4332" }}>
            Créer un compte gratuit
          </button>
        </div>
      </div>
    </div>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// USER VIEWS
// ═══════════════════════════════════════════════════════════════════════════════

function UserSubmitForm({ onBack }: { onBack: () => void }) {
  return (
    <div>
      <button onClick={onBack} className="flex items-center gap-1.5 text-sm font-medium mb-5 transition-colors" style={{ color: "#1B4332" }}>
        <ArrowLeft size={16} />Retour
      </button>
      <div className="max-w-2xl mx-auto">
        <h1 className="text-xl sm:text-2xl font-bold text-foreground mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>
          Soumettre une référence
        </h1>
        <p className="text-muted-foreground mb-6 text-sm">Proposez un document à intégrer au catalogue.</p>

        <div className="space-y-4">
          {/* Section card */}
          <div className="bg-white rounded-2xl border border-border p-5 space-y-4">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Informations générales</p>
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Titre du document *</label>
              <input className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted placeholder:text-muted-foreground" placeholder="ex. Introduction au droit administratif" />
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium text-foreground mb-1.5 block">Auteur(s) *</label>
                <input className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted placeholder:text-muted-foreground" placeholder="Nom, Prénom" />
              </div>
              <div>
                <label className="text-sm font-medium text-foreground mb-1.5 block">Année *</label>
                <input type="number" className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted placeholder:text-muted-foreground" placeholder="2024" />
              </div>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium text-foreground mb-1.5 block">Catégorie *</label>
                <select className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted text-foreground">
                  <option value="">Choisir...</option>
                  {["Droit", "Sciences", "Histoire", "Médecine", "Littérature", "Économie"].map(c => <option key={c}>{c}</option>)}
                </select>
              </div>
              <div>
                <label className="text-sm font-medium text-foreground mb-1.5 block">Éditeur</label>
                <input className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted placeholder:text-muted-foreground" placeholder="Maison d'édition" />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-border p-5 space-y-4">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Description</p>
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Mots-clés</label>
              <input className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted placeholder:text-muted-foreground" placeholder="droit public, Bénin, administration..." />
            </div>
            <div>
              <label className="text-sm font-medium text-foreground mb-1.5 block">Résumé</label>
              <textarea rows={4} className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted resize-none placeholder:text-muted-foreground" placeholder="Décrivez brièvement le contenu et le public cible..." />
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-border p-5">
            <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-4">Fichier</p>
            <div className="border-2 border-dashed border-border rounded-2xl p-8 text-center cursor-pointer hover:bg-muted/50 transition-colors">
              <Upload size={28} className="mx-auto mb-3 text-muted-foreground" />
              <p className="text-sm text-foreground font-medium">Déposer votre fichier ici</p>
              <p className="text-xs text-muted-foreground mt-1">PDF uniquement · 50 Mo maximum</p>
              <button className="mt-4 px-5 py-2 rounded-xl border border-border text-sm font-medium text-foreground hover:bg-muted transition-colors">
                Parcourir
              </button>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row gap-3 pt-1">
            <button className="flex-1 py-3.5 text-white rounded-2xl text-sm font-semibold transition-opacity hover:opacity-90" style={{ backgroundColor: "#1B4332" }}>
              Soumettre la demande
            </button>
            <button onClick={onBack} className="sm:w-32 py-3.5 rounded-2xl text-sm font-medium border border-border hover:bg-muted transition-colors text-foreground">
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function UserDashboard({ nav, setNav }: { nav: string; setNav: (n: string) => void }) {
  if (nav === "submit") return <UserSubmitForm onBack={() => setNav("dashboard")} />;

  return (
    <div className="max-w-4xl mx-auto">
      {/* Greeting */}
      <div className="mb-6">
        <p className="text-muted-foreground text-sm">Bonjour 👋</p>
        <h1 className="text-xl sm:text-2xl font-bold text-foreground mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
          Fatou Camara
        </h1>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <StatCard icon={FileText} label="Demandes" value={5} color="bg-[#1B4332]" />
        <StatCard icon={Clock} label="En attente" value={2} color="bg-amber-500" />
        <StatCard icon={CheckCircle} label="Publiées" value={2} color="bg-green-600" />
        <StatCard icon={XCircle} label="Refusées" value={1} color="bg-red-500" />
      </div>

      {/* Quick action */}
      <button
        onClick={() => setNav("submit")}
        className="w-full flex items-center justify-between p-4 bg-white rounded-2xl border border-border shadow-sm hover:shadow-md transition-all mb-6 group"
      >
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ backgroundColor: "#1B4332" }}>
            <Plus size={18} className="text-white" />
          </div>
          <div className="text-left">
            <p className="text-sm font-semibold text-foreground">Soumettre une référence</p>
            <p className="text-xs text-muted-foreground">Proposer un nouveau document</p>
          </div>
        </div>
        <ChevronRight size={18} className="text-muted-foreground group-hover:text-foreground transition-colors" />
      </button>

      {/* My requests */}
      <SectionTitle action={<button className="text-xs font-medium" style={{ color: "#1B4332" }}>Voir tout</button>}>
        Mes demandes de dépôt
      </SectionTitle>

      <div className="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
        {depositRequests.map((req, i) => (
          <div key={req.id} className={`p-4 hover:bg-muted/30 transition-colors ${i < depositRequests.length - 1 ? "border-b border-border" : ""}`}>
            <div className="flex items-start justify-between gap-3 mb-2">
              <p className="text-sm font-semibold text-foreground flex-1 leading-snug">{req.title}</p>
              <StatusBadge status={req.status} />
            </div>
            <p className="text-xs text-muted-foreground mb-2">Soumis le {req.submitted}{req.manager ? ` · ${req.manager}` : ""}</p>

            {req.status === "rejected" && req.justification && (
              <div className="mt-2 px-3 py-2.5 bg-red-50 border border-red-100 rounded-xl text-xs text-red-700 leading-relaxed">
                <span className="font-semibold">Motif : </span>{req.justification}
              </div>
            )}

            {/* Progress */}
            <div className="flex items-center gap-2 mt-3">
              {["Soumis", "Examen", "Décision", "Publié"].map((step, idx) => {
                const prog = req.status === "published" ? 4 : req.status === "approved" ? 3 : req.status === "rejected" ? 2 : req.status === "second_opinion" ? 2 : 1;
                return (
                  <div key={step} className="flex items-center gap-2">
                    <div className="flex flex-col items-center gap-1">
                      <div className={`w-2 h-2 rounded-full ${idx < prog ? "bg-[#1B4332]" : "bg-gray-200"}`} />
                      <span className="text-[9px] text-muted-foreground hidden sm:block">{step}</span>
                    </div>
                    {idx < 3 && <div className={`h-px flex-1 w-6 sm:w-10 ${idx < prog - 1 ? "bg-[#1B4332]" : "bg-gray-200"}`} />}
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// HR MANAGER VIEWS
// ═══════════════════════════════════════════════════════════════════════════════

function HRDashboard({ nav }: { nav: string }) {
  if (nav === "users") {
    return (
      <div className="max-w-5xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
          <div>
            <h1 className="text-xl sm:text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
              Utilisateurs
            </h1>
            <p className="text-muted-foreground text-sm mt-0.5">{allUsers.length} comptes</p>
          </div>
          <button className="flex items-center justify-center gap-2 px-4 py-2.5 text-white rounded-xl text-sm font-semibold self-start sm:self-auto" style={{ backgroundColor: "#1B4332" }}>
            <Plus size={15} />Nouveau compte
          </button>
        </div>

        {/* Search & filters */}
        <div className="flex flex-col sm:flex-row gap-2 mb-4">
          <div className="flex-1 flex items-center gap-2 bg-white border border-border rounded-xl px-3.5 py-2.5">
            <Search size={15} className="text-muted-foreground flex-shrink-0" />
            <input placeholder="Rechercher..." className="flex-1 text-sm outline-none bg-transparent text-foreground placeholder:text-muted-foreground" />
          </div>
          <select className="px-3.5 py-2.5 border border-border rounded-xl text-sm bg-white text-foreground outline-none">
            <option>Tous les rôles</option>
            <option>Utilisateur</option>
            <option>Responsable</option>
          </select>
        </div>

        {/* Desktop table */}
        <div className="hidden md:block bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-muted/40">
                {["Utilisateur", "Rôle", "Statut", "Inscrit le", "Demandes", ""].map(h => (
                  <th key={h} className="px-5 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {allUsers.map(user => (
                <tr key={user.id} className="hover:bg-muted/20 transition-colors">
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-3">
                      <Avatar name={user.name} size="sm" />
                      <div>
                        <p className="text-sm font-semibold text-foreground">{user.name}</p>
                        <p className="text-xs text-muted-foreground">{user.email}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-5 py-3.5 text-sm text-foreground">
                    {user.role === "user" ? "Utilisateur" : user.role === "manager_hr" ? "Resp. RH" : "Resp. Demandes"}
                  </td>
                  <td className="px-5 py-3.5"><StatusBadge status={user.status} /></td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground">{user.joined}</td>
                  <td className="px-5 py-3.5 text-sm text-foreground">{user.requests}</td>
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-1 justify-end">
                      <button className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors"><Edit2 size={14} /></button>
                      <button className={`p-1.5 rounded-lg transition-colors ${user.status === "active" ? "text-red-400 hover:bg-red-50" : "text-green-500 hover:bg-green-50"}`}>
                        {user.status === "active" ? <UserX size={14} /> : <UserCheck size={14} />}
                      </button>
                      <button className="p-1.5 text-muted-foreground hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors"><Trash2 size={14} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mobile card list */}
        <div className="md:hidden space-y-2">
          {allUsers.map(user => (
            <div key={user.id} className="bg-white rounded-2xl border border-border p-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3 flex-1 min-w-0">
                  <Avatar name={user.name} />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm font-semibold text-foreground truncate">{user.name}</p>
                    <p className="text-xs text-muted-foreground truncate">{user.email}</p>
                  </div>
                </div>
                <div className="flex items-center gap-2 flex-shrink-0">
                  <StatusBadge status={user.status} />
                  <button className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors">
                    <MoreHorizontal size={16} />
                  </button>
                </div>
              </div>
              <div className="flex items-center gap-4 mt-3 pt-3 border-t border-border text-xs text-muted-foreground">
                <span>{user.role === "user" ? "Utilisateur" : "Responsable"}</span>
                <span>·</span>
                <span>Inscrit le {user.joined}</span>
                <span>·</span>
                <span>{user.requests} demande{user.requests !== 1 ? "s" : ""}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      <div className="mb-6">
        <p className="text-muted-foreground text-sm">Tableau de bord</p>
        <h1 className="text-xl sm:text-2xl font-bold text-foreground mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
          Gestion RH
        </h1>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <StatCard icon={Users} label="Utilisateurs" value={1247} trend="+34 ce mois" color="bg-[#1B4332]" />
        <StatCard icon={UserCheck} label="Actifs" value={1198} color="bg-green-600" />
        <StatCard icon={UserX} label="Inactifs" value={49} color="bg-amber-500" />
        <StatCard icon={Plus} label="Nouveaux" value={34} trend="+18%" color="bg-blue-500" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-2 bg-white rounded-2xl border border-border shadow-sm p-5">
          <SectionTitle>Inscriptions mensuelles</SectionTitle>
          <ResponsiveContainer width="100%" height={200}>
            <AreaChart data={userGrowth} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="rgba(60,60,67,0.06)" />
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6E6E73" }} />
              <YAxis tick={{ fontSize: 11, fill: "#6E6E73" }} />
              <Tooltip contentStyle={{ backgroundColor: "#fff", border: "1px solid rgba(60,60,67,0.13)", borderRadius: 12 }} />
              <Area type="monotone" dataKey="inscrits" name="Inscriptions" stroke="#1B4332" fill="rgba(27,67,50,0.1)" strokeWidth={2} />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white rounded-2xl border border-border shadow-sm p-5">
          <SectionTitle>Activité récente</SectionTitle>
          <div className="space-y-3.5">
            {[
              { action: "Compte activé", user: "K. Amoussou", time: "Il y a 2h", color: "bg-green-500" },
              { action: "Compte créé", user: "P. Mensah", time: "Il y a 5h", color: "bg-blue-500" },
              { action: "Compte suspendu", user: "A. Diallo", time: "Hier", color: "bg-amber-500" },
              { action: "Rôle modifié", user: "T. Kpinsoté", time: "Hier", color: "bg-purple-500" },
              { action: "Mdp réinitialisé", user: "F. Camara", time: "Il y a 3j", color: "bg-blue-400" },
            ].map((item, i) => (
              <div key={i} className="flex items-start gap-3">
                <div className={`w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1.5 ${item.color}`} />
                <div>
                  <p className="text-xs text-foreground"><span className="font-semibold">{item.action}</span> — <span className="text-muted-foreground">{item.user}</span></p>
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

// ═══════════════════════════════════════════════════════════════════════════════
// REQUEST MANAGER VIEWS
// ═══════════════════════════════════════════════════════════════════════════════

function ManagerDashboard({ nav }: { nav: string }) {
  const [selectedId, setSelectedId] = useState<number>(1);
  const [decision, setDecision] = useState<"approve" | "reject" | null>(null);
  const [justification, setJustification] = useState("");
  const [mobileDetail, setMobileDetail] = useState(false);

  const req = depositRequests.find(r => r.id === selectedId);

  if (nav === "requests") {
    return (
      <div className="max-w-5xl mx-auto">
        <div className="mb-5">
          <h1 className="text-xl sm:text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
            Demandes assignées
          </h1>
          <p className="text-muted-foreground text-sm mt-0.5">5 demandes en attente d'examen</p>
        </div>

        {/* Mobile: list → detail flow */}
        {mobileDetail && req ? (
          <div className="md:hidden">
            <button onClick={() => { setMobileDetail(false); setDecision(null); }} className="flex items-center gap-1.5 text-sm font-medium mb-4" style={{ color: "#1B4332" }}>
              <ArrowLeft size={16} />Toutes les demandes
            </button>
            <ReviewPanel req={req} decision={decision} setDecision={setDecision} justification={justification} setJustification={setJustification} />
          </div>
        ) : (
          <>
            {/* Mobile list */}
            <div className="md:hidden space-y-2">
              {depositRequests.map(r => (
                <button key={r.id} onClick={() => { setSelectedId(r.id); setMobileDetail(true); setDecision(null); }}
                  className="w-full bg-white rounded-2xl border border-border p-4 text-left hover:shadow-sm transition-all">
                  <div className="flex items-start justify-between gap-2 mb-1">
                    <p className="text-sm font-semibold text-foreground flex-1 leading-snug">{r.title}</p>
                    <StatusBadge status={r.status} />
                  </div>
                  <p className="text-xs text-muted-foreground">{r.author} · {r.submitted}</p>
                  <div className="flex items-center justify-end mt-2">
                    <ChevronRight size={16} className="text-muted-foreground" />
                  </div>
                </button>
              ))}
            </div>

            {/* Desktop: list + panel */}
            <div className="hidden md:grid gap-5" style={{ gridTemplateColumns: "1fr 1.7fr", maxHeight: "calc(100vh - 220px)" }}>
              <div className="bg-white rounded-2xl border border-border shadow-sm flex flex-col overflow-hidden">
                <div className="px-4 py-3 border-b border-border flex-shrink-0">
                  <div className="flex items-center gap-2 bg-muted rounded-xl px-3 py-2">
                    <Search size={13} className="text-muted-foreground" />
                    <input placeholder="Filtrer..." className="flex-1 text-sm outline-none bg-transparent text-foreground placeholder:text-muted-foreground" />
                  </div>
                </div>
                <div className="flex-1 overflow-y-auto">
                  {depositRequests.map(r => (
                    <button key={r.id} onClick={() => { setSelectedId(r.id); setDecision(null); setJustification(""); }}
                      className={`w-full text-left px-4 py-3.5 border-b border-border last:border-0 transition-all ${
                        selectedId === r.id ? "border-l-[3px] border-l-[#1B4332] pl-3.5" : "hover:bg-muted/30"
                      }`}
                      style={selectedId === r.id ? { backgroundColor: "rgba(27,67,50,0.04)" } : {}}
                    >
                      <p className="text-sm font-semibold text-foreground line-clamp-2 leading-snug mb-1">{r.title}</p>
                      <div className="flex items-center justify-between">
                        <p className="text-xs text-muted-foreground">{r.author} · {r.submitted}</p>
                        <StatusBadge status={r.status} />
                      </div>
                    </button>
                  ))}
                </div>
              </div>

              <div className="bg-white rounded-2xl border border-border shadow-sm overflow-y-auto">
                {req && <ReviewPanel req={req} decision={decision} setDecision={setDecision} justification={justification} setJustification={setJustification} />}
              </div>
            </div>
          </>
        )}
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      <div className="mb-6">
        <p className="text-muted-foreground text-sm">Tableau de bord</p>
        <h1 className="text-xl sm:text-2xl font-bold text-foreground mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
          Gestion des demandes
        </h1>
      </div>
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <StatCard icon={Clock} label="En attente" value={8} color="bg-amber-500" />
        <StatCard icon={CheckCircle} label="Validées" value={14} trend="+4 ce mois" color="bg-[#1B4332]" />
        <StatCard icon={XCircle} label="Refusées" value={3} color="bg-red-500" />
        <StatCard icon={RefreshCw} label="Seconds avis" value={2} color="bg-purple-600" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-2 bg-white rounded-2xl border border-border shadow-sm">
          <div className="px-5 py-4 border-b border-border">
            <SectionTitle>Demandes récentes assignées</SectionTitle>
          </div>
          {depositRequests.map((r, i) => (
            <div key={r.id} className={`px-5 py-4 flex items-center justify-between gap-4 hover:bg-muted/20 transition-colors ${i < depositRequests.length - 1 ? "border-b border-border" : ""}`}>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-foreground truncate">{r.title}</p>
                <p className="text-xs text-muted-foreground mt-0.5">{r.author} · {r.submitted}</p>
              </div>
              <div className="flex items-center gap-3 flex-shrink-0">
                <StatusBadge status={r.status} />
                <ChevronRight size={15} className="text-muted-foreground" />
              </div>
            </div>
          ))}
        </div>

        <div className="bg-white rounded-2xl border border-border shadow-sm p-5">
          <SectionTitle>Mes performances</SectionTitle>
          <div className="space-y-4">
            {[{ label: "Validées", value: 47, total: 60, color: "#1B4332" }, { label: "Refusées", value: 13, total: 60, color: "#FF9F0A" }].map(s => (
              <div key={s.label}>
                <div className="flex justify-between mb-1.5">
                  <span className="text-sm text-foreground">{s.label}</span>
                  <span className="text-sm font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>{s.value}</span>
                </div>
                <div className="h-2 bg-muted rounded-full overflow-hidden">
                  <div className="h-full rounded-full" style={{ width: `${(s.value / s.total) * 100}%`, backgroundColor: s.color }} />
                </div>
              </div>
            ))}
            <div className="pt-4 border-t border-border">
              <p className="text-xs text-muted-foreground">Délai moyen de traitement</p>
              <p className="text-3xl font-bold text-foreground mt-1" style={{ fontFamily: "'Playfair Display', serif" }}>
                2,4 <span className="text-base font-normal text-muted-foreground">jours</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function ReviewPanel({ req, decision, setDecision, justification, setJustification }: {
  req: typeof depositRequests[0];
  decision: "approve" | "reject" | null;
  setDecision: (d: "approve" | "reject" | null) => void;
  justification: string;
  setJustification: (s: string) => void;
}) {
  return (
    <div>
      <div className="px-5 sm:px-6 py-5 border-b border-border">
        <div className="flex items-start justify-between gap-3">
          <div className="flex-1 min-w-0">
            <h2 className="text-lg sm:text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
              {req.title}
            </h2>
            <p className="text-sm text-muted-foreground mt-1">{req.author} · {req.submitted}</p>
          </div>
          <StatusBadge status={req.status} />
        </div>
      </div>
      <div className="px-5 sm:px-6 py-5 space-y-5">
        <div className="grid grid-cols-2 gap-4">
          {[["Catégorie", "Sciences naturelles"], ["Année", "2023"], ["Éditeur", "UNSTIM Éditions"], ["Format", "PDF · 4,2 Mo"]].map(([k, v]) => (
            <div key={k}>
              <p className="text-[10px] text-muted-foreground uppercase tracking-wide font-semibold mb-0.5">{k}</p>
              <p className="text-sm text-foreground font-medium">{v}</p>
            </div>
          ))}
        </div>
        <div>
          <p className="text-[10px] text-muted-foreground uppercase tracking-wide font-semibold mb-1.5">Résumé</p>
          <p className="text-sm text-foreground leading-relaxed">Ouvrage académique traitant des fondements de la biologie cellulaire dans le contexte ouest-africain, avec études de cas et exercices corrigés.</p>
        </div>
        <div className="flex gap-2">
          <button className="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium border border-border hover:bg-muted transition-colors"><Eye size={14} />Prévisualiser</button>
          <button className="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium border border-border hover:bg-muted transition-colors"><Download size={14} />Télécharger</button>
        </div>

        {(req.status === "pending" || req.status === "second_opinion") && (
          <div className="border-t border-border pt-5">
            <p className="text-sm font-semibold text-foreground mb-3">Votre décision</p>
            <div className="flex gap-2 mb-4">
              <button onClick={() => setDecision("approve")}
                className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all ${decision === "approve" ? "border-green-500 bg-green-50 text-green-700" : "border-border text-muted-foreground hover:border-green-300"}`}>
                <CheckCircle size={15} />Valider
              </button>
              <button onClick={() => setDecision("reject")}
                className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all ${decision === "reject" ? "border-red-500 bg-red-50 text-red-700" : "border-border text-muted-foreground hover:border-red-300"}`}>
                <XCircle size={15} />Refuser
              </button>
            </div>
            {decision && (
              <div className="space-y-3">
                <textarea rows={3} value={justification} onChange={e => setJustification(e.target.value)}
                  placeholder={decision === "reject" ? "Justification obligatoire..." : "Commentaire optionnel..."}
                  className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted resize-none placeholder:text-muted-foreground" />
                <button className={`w-full py-3 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 ${decision === "approve" ? "bg-green-600" : "bg-red-500"}`}>
                  {decision === "approve" ? "Confirmer et transmettre" : "Confirmer le refus"}
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// ADMIN VIEWS
// ═══════════════════════════════════════════════════════════════════════════════

function AdminDashboard({ nav }: { nav: string }) {
  const [reqFilter, setReqFilter] = useState("Toutes");
  const [overrideId, setOverrideId] = useState<number | null>(null);
  const [overrideJust, setOverrideJust] = useState("");

  if (nav === "requests") {
    const filters = ["Toutes", "En attente", "Validées", "Refusées", "Publiées", "Second avis"];
    const filterMap: Record<string, string> = { "En attente": "pending", Validées: "approved", Refusées: "rejected", Publiées: "published", "Second avis": "second_opinion" };
    const filtered = reqFilter === "Toutes" ? depositRequests : depositRequests.filter(r => r.status === filterMap[reqFilter]);

    return (
      <div className="max-w-5xl mx-auto">
        <div className="mb-5">
          <h1 className="text-xl sm:text-2xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
            Circuit de validation
          </h1>
          <p className="text-muted-foreground text-sm mt-0.5">Supervision de toutes les demandes</p>
        </div>

        {/* Filter pills */}
        <div className="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 mb-4">
          {filters.map(f => (
            <button key={f} onClick={() => setReqFilter(f)}
              className={`px-3.5 py-1.5 text-xs rounded-full font-semibold whitespace-nowrap transition-all flex-shrink-0 ${reqFilter === f ? "text-white" : "bg-white border border-border text-muted-foreground hover:text-foreground"}`}
              style={reqFilter === f ? { backgroundColor: "#1B4332" } : {}}>
              {f}
            </button>
          ))}
        </div>

        {/* Desktop table */}
        <div className="hidden md:block bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-muted/40">
                {["Titre", "Auteur", "Soumis le", "Responsable", "Statut", "Actions"].map(h => (
                  <th key={h} className="px-5 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {filtered.map(r => (
                <tr key={r.id} className="hover:bg-muted/20 transition-colors">
                  <td className="px-5 py-3.5 max-w-xs"><p className="text-sm font-semibold text-foreground truncate">{r.title}</p></td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground whitespace-nowrap">{r.author}</td>
                  <td className="px-5 py-3.5 text-sm text-muted-foreground whitespace-nowrap">{r.submitted}</td>
                  <td className="px-5 py-3.5 text-sm text-foreground">{r.manager ?? "—"}</td>
                  <td className="px-5 py-3.5"><StatusBadge status={r.status} /></td>
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-1.5">
                      {r.status === "rejected" && (
                        <button onClick={() => setOverrideId(r.id)} className="px-2.5 py-1 text-xs rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors font-semibold">
                          Invalider
                        </button>
                      )}
                      {r.status === "approved" && (
                        <button className="px-2.5 py-1 text-xs rounded-lg border border-green-300 text-green-700 hover:bg-green-50 transition-colors font-semibold">
                          Publier
                        </button>
                      )}
                      {r.status === "pending" && (
                        <button className="px-2.5 py-1 text-xs rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50 transition-colors font-semibold">
                          2nd avis
                        </button>
                      )}
                      <button className="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors"><Eye size={14} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mobile cards */}
        <div className="md:hidden space-y-2">
          {filtered.map(r => (
            <div key={r.id} className="bg-white rounded-2xl border border-border p-4">
              <div className="flex items-start justify-between gap-2 mb-2">
                <p className="text-sm font-semibold text-foreground flex-1 leading-snug">{r.title}</p>
                <StatusBadge status={r.status} />
              </div>
              <p className="text-xs text-muted-foreground mb-3">{r.author} · {r.submitted}{r.manager ? ` · ${r.manager}` : ""}</p>
              <div className="flex gap-2">
                {r.status === "rejected" && (
                  <button onClick={() => setOverrideId(r.id)} className="px-3 py-1.5 text-xs rounded-lg border border-amber-300 text-amber-700 bg-amber-50 font-semibold">
                    Invalider le refus
                  </button>
                )}
                {r.status === "approved" && (
                  <button className="px-3 py-1.5 text-xs rounded-lg border border-green-300 text-green-700 bg-green-50 font-semibold">Publier</button>
                )}
                {r.status === "pending" && (
                  <button className="px-3 py-1.5 text-xs rounded-lg border border-purple-300 text-purple-700 bg-purple-50 font-semibold">Second avis</button>
                )}
                <button className="ml-auto p-1.5 text-muted-foreground hover:text-foreground"><Eye size={16} /></button>
              </div>
            </div>
          ))}
        </div>

        {/* Override modal */}
        {overrideId !== null && (
          <div className="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center z-50 p-4" style={{ backdropFilter: "blur(8px)" }}>
            <div className="bg-white rounded-3xl sm:rounded-2xl border border-border p-6 sm:p-7 w-full sm:max-w-md shadow-2xl">
              <div className="flex items-start gap-3 mb-4">
                <div className="w-10 h-10 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                  <AlertCircle size={20} className="text-amber-600" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Invalider le refus
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1 leading-relaxed">
                    Vous passez outre la décision du responsable. La référence sera publiée et le responsable notifié.
                  </p>
                </div>
              </div>
              <div className="mb-4">
                <label className="text-sm font-semibold text-foreground mb-1.5 block">Justification *</label>
                <textarea rows={4} value={overrideJust} onChange={e => setOverrideJust(e.target.value)}
                  placeholder="Expliquez pourquoi le refus n'est pas fondé..."
                  className="w-full px-4 py-3 rounded-xl border border-border text-sm outline-none focus:ring-2 focus:ring-[#1B4332]/20 bg-muted resize-none placeholder:text-muted-foreground" />
              </div>
              <div className="flex flex-col sm:flex-row gap-2">
                <button className="flex-1 py-3 text-white rounded-xl text-sm font-semibold" style={{ backgroundColor: "#1B4332" }}>
                  Confirmer et publier
                </button>
                <button onClick={() => setOverrideId(null)} className="sm:w-28 py-3 rounded-xl text-sm font-medium border border-border hover:bg-muted transition-colors">
                  Annuler
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto">
      <div className="mb-6">
        <p className="text-muted-foreground text-sm">Tableau de bord</p>
        <h1 className="text-xl sm:text-2xl font-bold text-foreground mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
          Administration
        </h1>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <StatCard icon={BookOpen} label="Références" value={1247} trend="+34 ce mois" color="bg-[#1B4332]" />
        <StatCard icon={Users} label="Utilisateurs" value="4 892" trend="+127" color="bg-blue-500" />
        <StatCard icon={Clock} label="En cours" value={18} color="bg-amber-500" />
        <StatCard icon={Eye} label="Vues (30j)" value="28 400" trend="+12%" color="bg-purple-600" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <div className="lg:col-span-2 bg-white rounded-2xl border border-border shadow-sm p-5">
          <SectionTitle>
            <span>Activité des demandes</span>
          </SectionTitle>
          <ResponsiveContainer width="100%" height={200}>
            <BarChart data={statsData} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="rgba(60,60,67,0.06)" />
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6E6E73" }} />
              <YAxis tick={{ fontSize: 11, fill: "#6E6E73" }} />
              <Tooltip contentStyle={{ backgroundColor: "#fff", border: "1px solid rgba(60,60,67,0.13)", borderRadius: 12 }} />
              <Bar dataKey="publiées" name="Publiées" fill="#1B4332" radius={[4, 4, 0, 0]} />
              <Bar dataKey="refusées" name="Refusées" fill="#FF9F0A" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white rounded-2xl border border-border shadow-sm p-5">
          <SectionTitle>Par catégorie</SectionTitle>
          <ResponsiveContainer width="100%" height={140}>
            <PieChart>
              <Pie data={pieData} cx="50%" cy="50%" innerRadius={40} outerRadius={62} dataKey="value" paddingAngle={2}>
                {pieData.map((e, i) => <Cell key={i} fill={e.color} />)}
              </Pie>
              <Tooltip contentStyle={{ backgroundColor: "#fff", border: "1px solid rgba(60,60,67,0.13)", borderRadius: 12 }} formatter={v => [`${v} réf.`]} />
            </PieChart>
          </ResponsiveContainer>
          <div className="space-y-1.5 mt-3">
            {pieData.slice(0, 5).map(d => (
              <div key={d.name} className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-2.5 h-2.5 rounded-sm" style={{ backgroundColor: d.color }} />
                  <span className="text-xs text-muted-foreground">{d.name}</span>
                </div>
                <span className="text-xs font-bold text-foreground" style={{ fontFamily: "'DM Mono', monospace" }}>{d.value}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div className="bg-white rounded-2xl border border-border shadow-sm">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>Décisions en attente</h2>
            <span className="bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold">5 urgentes</span>
          </div>
          {depositRequests.filter(r => ["pending", "rejected", "second_opinion"].includes(r.status)).map((r, i, arr) => (
            <div key={r.id} className={`px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-muted/20 transition-colors ${i < arr.length - 1 ? "border-b border-border" : ""}`}>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-foreground truncate">{r.title}</p>
                <p className="text-xs text-muted-foreground mt-0.5">{r.author}</p>
              </div>
              <div className="flex items-center gap-2 flex-shrink-0">
                <StatusBadge status={r.status} />
                <ChevronRight size={15} className="text-muted-foreground" />
              </div>
            </div>
          ))}
        </div>

        <div className="bg-white rounded-2xl border border-border shadow-sm">
          <div className="px-5 py-4 border-b border-border">
            <h2 className="font-semibold text-foreground text-sm" style={{ fontFamily: "'Playfair Display', serif" }}>Journal d'activité</h2>
          </div>
          {[
            { msg: "Référence publiée : « Droit commercial »", time: "Il y a 1h", icon: CheckCircle, color: "text-green-500" },
            { msg: "Refus invalidé par l'admin pour « Biologie cellulaire »", time: "Il y a 3h", icon: RefreshCw, color: "text-amber-500" },
            { msg: "Second avis demandé : « Gouvernance locale »", time: "Il y a 5h", icon: MessageSquare, color: "text-purple-500" },
            { msg: "Nouvel utilisateur inscrit : T. Mensah", time: "Il y a 6h", icon: Users, color: "text-blue-500" },
            { msg: "Compte désactivé : A. Diallo", time: "Hier 14h22", icon: UserX, color: "text-red-500" },
          ].map((item, i, arr) => (
            <div key={i} className={`px-5 py-3.5 flex items-start gap-3 hover:bg-muted/20 transition-colors ${i < arr.length - 1 ? "border-b border-border" : ""}`}>
              <item.icon size={14} className={`mt-0.5 flex-shrink-0 ${item.color}`} />
              <div>
                <p className="text-xs text-foreground leading-snug">{item.msg}</p>
                <p className="text-[11px] text-muted-foreground mt-0.5">{item.time}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN APP
// ═══════════════════════════════════════════════════════════════════════════════

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
    { id: "dashboard", label: "Accueil", icon: Home },
    { id: "catalog", label: "Catalogue", icon: BookOpen },
    { id: "requests", label: "Demandes", icon: FileText, badge: 2 },
    { id: "submit", label: "Déposer", icon: Upload },
    { id: "profile", label: "Profil", icon: User },
  ];

  const hrNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Accueil", icon: Home },
    { id: "users", label: "Utilisateurs", icon: Users },
    { id: "logs", label: "Journal", icon: Activity },
    { id: "profile", label: "Profil", icon: User },
  ];

  const managerNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Accueil", icon: Home },
    { id: "requests", label: "Demandes", icon: FileText, badge: 8 },
    { id: "second", label: "Seconds avis", icon: RefreshCw, badge: 2 },
    { id: "profile", label: "Profil", icon: User },
  ];

  const adminNavItems: SidebarItem[] = [
    { id: "dashboard", label: "Accueil", icon: Home },
    { id: "requests", label: "Demandes", icon: FileText, badge: 5 },
    { id: "references", label: "Références", icon: BookOpen },
    { id: "users", label: "Utilisateurs", icon: Users },
    { id: "logs", label: "Journaux", icon: Activity },
    { id: "settings", label: "Réglages", icon: Settings },
  ];

  return (
    <div className="min-h-screen bg-background" style={{ fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, sans-serif" }}>
      {/* Preview toolbar */}
      <div className="flex items-center h-11 px-4 gap-1 overflow-x-auto no-scrollbar flex-shrink-0" style={{ backgroundColor: "#0A1A10" }}>
        <span className="text-[10px] text-white/25 font-semibold tracking-[0.2em] uppercase mr-3 whitespace-nowrap flex-shrink-0">
          Aperçu
        </span>
        <div className="h-3 w-px bg-white/10 mr-2 flex-shrink-0" />
        {roles.map(role => (
          <button
            key={role.id}
            onClick={() => setActiveRole(role.id)}
            className={`flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-medium whitespace-nowrap transition-all rounded-lg flex-shrink-0 ${
              activeRole === role.id ? "bg-white/15 text-white" : "text-white/35 hover:text-white/65"
            }`}
          >
            <role.icon size={12} />
            {role.label}
          </button>
        ))}
      </div>

      {activeRole === "visitor" && <VisitorPage />}

      {activeRole === "user" && (
        <AppShell sidebarItems={userNavItems} activeNav={userNav} onNavigate={setUserNav} userName="Fatou Camara" roleLabel="Utilisateur inscrit">
          <UserDashboard nav={userNav} setNav={setUserNav} />
        </AppShell>
      )}

      {activeRole === "hr" && (
        <AppShell sidebarItems={hrNavItems} activeNav={hrNav} onNavigate={setHrNav} userName="Marie Agbodossou" roleLabel="Responsable RH">
          <HRDashboard nav={hrNav} />
        </AppShell>
      )}

      {activeRole === "manager" && (
        <AppShell sidebarItems={managerNavItems} activeNav={managerNav} onNavigate={setManagerNav} userName="Pierre Houénou" roleLabel="Resp. Demandes">
          <ManagerDashboard nav={managerNav} />
        </AppShell>
      )}

      {activeRole === "admin" && (
        <AppShell sidebarItems={adminNavItems} activeNav={adminNav} onNavigate={setAdminNav} userName="Admin Principal" roleLabel="Administrateur">
          <AdminDashboard nav={adminNav} />
        </AppShell>
      )}
    </div>
  );
}
