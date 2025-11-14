'use client';

import { AppLayout } from '@/components/layout/AppLayout';
import { Card, CardHeader, CardBody } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import {
  UsersRound,
  CalendarDays,
  FileCheck,
  TrendingUp,
  ActivitySquare,
  Sunrise,
  PhoneCall,
  CircleDollarSign,
  ChevronRight,
  PlusCircle,
  Loader2,
  Pencil,
  Trash2,
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { useAuth } from '@/lib/hooks/useAuth';
import { dashboardService, appointmentsService } from '@/lib/services';
import { DashboardStats, Appointment } from '@/lib/types';
import { CreateAppointmentModal } from '@/components/modals/CreateAppointmentModal';
import { EditAppointmentModal } from '@/components/modals/EditAppointmentModal';
import { DeleteConfirmModal } from '@/components/modals/DeleteConfirmModal';

export default function DashboardPage() {
  useAuth(); // Protéger la page - redirige vers login si non connecté

  const [currentDate, setCurrentDate] = useState<string>('');
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [mounted, setMounted] = useState(false);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [selectedAppointment, setSelectedAppointment] = useState<Appointment | null>(null);

  // Fonction pour charger les données (réutilisable pour le refresh)
  const loadDashboardData = async () => {
    try {
      const [statsData, appointmentsData] = await Promise.all([
        dashboardService.getStats(),
        dashboardService.getTodayAppointments(),
      ]);

      setStats(statsData);
      setAppointments(appointmentsData);
      setError('');
    } catch (err: any) {
      console.error('Erreur chargement dashboard:', err);
      setError(err.message || 'Erreur lors du chargement des données');
    } finally {
      setLoading(false);
    }
  };

  // Fonction pour ouvrir le modal d'édition
  const handleEditAppointment = (appointment: Appointment) => {
    setSelectedAppointment(appointment);
    setIsEditModalOpen(true);
  };

  // Fonction pour ouvrir le modal de suppression
  const handleDeleteClick = (appointment: Appointment) => {
    setSelectedAppointment(appointment);
    setIsDeleteModalOpen(true);
  };

  // Fonction pour supprimer le rendez-vous
  const handleDeleteConfirm = async () => {
    if (!selectedAppointment) return;
    await appointmentsService.deleteAppointment(selectedAppointment.id);
    await loadDashboardData(); // Rafraîchir le dashboard
  };

  useEffect(() => {
    // Marquer comme monté côté client
    setMounted(true);
    setCurrentDate(new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }));

    // Charger les données au montage
    loadDashboardData();
  }, []);

  // Activités récentes (statiques pour l'instant)
  const recentActivities = [
    {
      id: 1,
      type: 'rdv',
      title: 'Rendez-vous avec M. Dupont',
      time: 'Il y a 2 heures',
      status: 'completed',
      icon: CalendarDays,
      client: 'Installation 6kWc',
    },
    {
      id: 2,
      type: 'devis',
      title: 'Devis #2024-087 envoyé',
      time: 'Il y a 4 heures',
      status: 'pending',
      icon: FileCheck,
      client: 'Mme Martin - 9kWc',
    },
    {
      id: 3,
      type: 'client',
      title: 'Nouveau client : Mme Rousseau',
      time: 'Hier',
      status: 'new',
      icon: UsersRound,
      client: 'Prospect chaud',
    },
  ];

  const getActivityBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <span className="text-sm text-green-600 font-medium">Terminé</span>;
      case 'pending':
        return <span className="text-sm text-amber-600 font-medium">En attente</span>;
      case 'new':
        return <span className="text-sm text-blue-600 font-medium">Nouveau</span>;
      default:
        return null;
    }
  };

  // Afficher un loader pendant le chargement
  if (loading) {
    return (
      <AppLayout>
        <div className="flex items-center justify-center min-h-[60vh]">
          <div className="text-center">
            <Loader2 className="h-12 w-12 text-indigo-600 animate-spin mx-auto mb-4" />
            <p className="text-lg text-gray-600">Chargement du dashboard...</p>
          </div>
        </div>
      </AppLayout>
    );
  }

  // Afficher une erreur si le chargement a échoué
  if (error) {
    return (
      <AppLayout>
        <div className="bg-red-50 border border-red-200 rounded-lg p-6">
          <p className="text-red-700">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Réessayer
          </button>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <div className="space-y-8">
        <div className="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-7 border border-indigo-200">
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-lg font-display font-semibold text-gray-900 flex items-center">
              <CalendarDays className="h-6 w-6 mr-3 text-indigo-600" />
              Rendez-vous du jour
            </h2>
            <span className="text-base text-gray-600 bg-white px-4 py-2 rounded-full" suppressHydrationWarning>
              {mounted ? currentDate : ''}
            </span>
          </div>

          {appointments.length > 0 ? (
            <div className="space-y-4">
              {appointments.map((appointment) => (
                <div
                  key={appointment.id}
                  className="flex items-center justify-between p-5 bg-white rounded-lg hover:shadow-md transition-all border border-indigo-100"
                >
                  <div className="flex items-center space-x-4">
                    <div className={`text-xl font-bold ${appointment.status === 'urgent' ? 'text-red-600' : 'text-indigo-700'}`}>
                      {appointment.time}
                    </div>
                    <div className="h-10 w-px bg-gray-300"></div>
                    <div>
                      <div className="font-display font-medium text-gray-900 text-base">{appointment.client.fullName}</div>
                      <div className="text-sm text-gray-500 flex items-center mt-1">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mr-2 ${
                          appointment.type === 'Installation' ? 'bg-green-100 text-green-700' :
                          appointment.type === 'Visite technique' ? 'bg-blue-100 text-blue-700' :
                          'bg-purple-100 text-purple-700'
                        }`}>
                          {appointment.type}
                        </span>
                        • {appointment.address.split(',')[0]}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => handleEditAppointment(appointment)}
                      className="p-3 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                      title="Modifier"
                    >
                      <Pencil className="h-5 w-5" />
                    </button>
                    <button
                      onClick={() => handleDeleteClick(appointment)}
                      className="p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      title="Supprimer"
                    >
                      <Trash2 className="h-5 w-5" />
                    </button>
                    <a
                      href={`tel:${appointment.client.phone}`}
                      className="p-3 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                      title="Appeler"
                    >
                      <PhoneCall className="h-5 w-5" />
                    </a>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-10 bg-white rounded-lg">
              <CalendarDays className="h-14 w-14 text-indigo-200 mx-auto mb-3" />
              <p className="text-base text-gray-500">Aucun rendez-vous aujourd&apos;hui</p>
            </div>
          )}
        </div>

        {stats && (
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-5">
            <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
              <div className="flex items-center justify-between mb-4">
                <CalendarDays className="h-8 w-8 text-blue-200" />
                <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                  stats.appointmentsThisMonth.changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
                }`}>
                  {stats.appointmentsThisMonth.change}
                </span>
              </div>
              <div>
                <p className="text-3xl font-display font-bold">{stats.appointmentsThisMonth.value}</p>
                <p className="text-sm text-blue-100 mt-1">RDV ce mois</p>
              </div>
            </div>

            <div className="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
              <div className="flex items-center justify-between mb-4">
                <UsersRound className="h-8 w-8 text-green-200" />
                <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                  stats.activeClients.changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
                }`}>
                  {stats.activeClients.change}
                </span>
              </div>
              <div>
                <p className="text-3xl font-display font-bold">{stats.activeClients.value}</p>
                <p className="text-sm text-green-100 mt-1">Clients actifs</p>
              </div>
            </div>

            <div className="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
              <div className="flex items-center justify-between mb-4">
                <FileCheck className="h-8 w-8 text-amber-200" />
                <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                  stats.quotesInProgress.changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
                }`}>
                  {stats.quotesInProgress.change}
                </span>
              </div>
              <div>
                <p className="text-3xl font-display font-bold">{stats.quotesInProgress.value}</p>
                <p className="text-sm text-amber-100 mt-1">Devis en cours</p>
              </div>
            </div>

            <div className="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
              <div className="flex items-center justify-between mb-4">
                <CircleDollarSign className="h-8 w-8 text-purple-200" />
                <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                  stats.revenue.changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
                }`}>
                  {stats.revenue.change}
                </span>
              </div>
              <div>
                <p className="text-3xl font-display font-bold">{stats.revenue.value}</p>
                <p className="text-sm text-purple-100 mt-1">CA du mois</p>
              </div>
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <Card className="bg-white rounded-lg border border-gray-200">
            <CardHeader
              title="Activité récente"
              icon={ActivitySquare}
              action={
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-sm text-gray-600 hover:text-gray-900"
                >
                  Voir tout
                </Button>
              }
            />
            <CardBody className="p-6">
              <div className="space-y-5">
                {recentActivities.map((activity) => (
                  <div key={activity.id} className="flex items-center justify-between py-2">
                    <div className="flex items-center space-x-4">
                      <activity.icon className={`h-5 w-5 text-gray-400`} />
                      <div>
                        <p className="text-base font-display font-medium text-gray-900">{activity.title}</p>
                        <p className="text-sm text-gray-500 mt-1">{activity.time}</p>
                      </div>
                    </div>
                    {getActivityBadge(activity.status)}
                  </div>
                ))}
              </div>
            </CardBody>
          </Card>

          <Card className="bg-white rounded-xl shadow-sm border border-gray-200">
            <CardHeader
              title={
                <span className="flex items-center">
                  <TrendingUp className="h-6 w-6 mr-2 text-green-600" />
                  Performance
                </span>
              }
            />
            <CardBody className="p-6">
              <div className="space-y-5">
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-blue-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Visites effectuées</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <span className="text-base font-bold text-gray-900">18/25</span>
                    <div className="w-20 bg-gray-200 rounded-full h-2.5">
                      <div className="bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full" style={{width: '72%'}}></div>
                    </div>
                  </div>
                </div>
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Devis signés</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <span className="text-base font-bold text-gray-900">5/8</span>
                    <div className="w-20 bg-gray-200 rounded-full h-2.5">
                      <div className="bg-gradient-to-r from-green-500 to-emerald-600 h-2.5 rounded-full" style={{width: '62.5%'}}></div>
                    </div>
                  </div>
                </div>
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-purple-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Taux de conversion</span>
                  </div>
                  <span className="text-base font-bold text-purple-600">62.5%</span>
                </div>
                <div className="pt-4 border-t">
                  <div className="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg">
                    <span className="text-base font-medium text-gray-900 flex items-center">
                      <CircleDollarSign className="h-5 w-5 mr-2 text-amber-600" />
                      Commission estimée
                    </span>
                    <span className="text-xl font-display font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                      2 850€
                    </span>
                  </div>
                </div>
              </div>
            </CardBody>
          </Card>
        </div>

        <div className="bg-gradient-to-r from-teal-50 via-blue-50 to-purple-50 rounded-xl p-7 border border-teal-200">
          <h3 className="text-base font-display font-semibold text-gray-900 mb-5 flex items-center">
            <PlusCircle className="h-5 w-5 mr-2 text-teal-600" />
            Actions rapides
          </h3>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button
              onClick={() => setIsCreateModalOpen(true)}
              className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-blue-300 hover:shadow-md transition-all group"
            >
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg group-hover:scale-110 transition-transform">
                  <CalendarDays className="h-6 w-6 text-blue-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Nouveau RDV</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-green-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg group-hover:scale-110 transition-transform">
                  <UsersRound className="h-6 w-6 text-green-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Nouveau client</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-amber-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-amber-100 to-orange-100 rounded-lg group-hover:scale-110 transition-transform">
                  <FileCheck className="h-6 w-6 text-amber-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Créer devis</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-purple-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg group-hover:scale-110 transition-transform">
                  <Sunrise className="h-6 w-6 text-purple-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Étude solaire</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      {/* Modal de création de rendez-vous */}
      <CreateAppointmentModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        onSuccess={loadDashboardData}
      />

      {/* Modal d'édition de rendez-vous */}
      <EditAppointmentModal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        onSuccess={loadDashboardData}
        appointment={selectedAppointment}
      />

      {/* Modal de confirmation de suppression */}
      <DeleteConfirmModal
        isOpen={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        onConfirm={handleDeleteConfirm}
        title="Supprimer le rendez-vous"
        message={`Êtes-vous sûr de vouloir supprimer le rendez-vous avec ${selectedAppointment?.client.fullName} ? Cette action est irréversible.`}
      />
    </AppLayout>
  );
}
