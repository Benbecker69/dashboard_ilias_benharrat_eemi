'use client';

import { useState, useEffect } from 'react';
import { X, Calendar, User, MapPin, FileText } from 'lucide-react';
import { appointmentsService } from '@/lib/services/appointments';
import { clientsService } from '@/lib/services';
import { Appointment } from '@/lib/types';

interface EditAppointmentModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  appointment: Appointment | null;
}

export function EditAppointmentModal({ isOpen, onClose, onSuccess, appointment }: EditAppointmentModalProps) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [clients, setClients] = useState<Array<{ id: number; fullName: string }>>([]);

  const [formData, setFormData] = useState({
    clientId: '',
    appointmentDate: '',
    appointmentTime: '',
    type: 'Installation',
    status: 'scheduled',
    address: '',
    notes: '',
  });

  // Charger les clients et pré-remplir le formulaire
  useEffect(() => {
    if (isOpen && appointment) {
      loadClients();

      // Pré-remplir le formulaire avec les données du rendez-vous
      const date = new Date(appointment.appointmentDate);
      const dateStr = date.toISOString().split('T')[0];
      const timeStr = date.toTimeString().slice(0, 5);

      setFormData({
        clientId: appointment.client.id.toString(),
        appointmentDate: dateStr,
        appointmentTime: timeStr,
        type: appointment.type,
        status: appointment.status,
        address: appointment.address,
        notes: appointment.notes || '',
      });
    }
  }, [isOpen, appointment]);

  const loadClients = async () => {
    try {
      const response = await clientsService.getClients({ limit: 100, status: 'all' });
      setClients(response.data.map(c => ({ id: c.id, fullName: c.fullName })));
    } catch (err) {
      console.error('Erreur chargement clients:', err);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!appointment) return;

    setLoading(true);
    setError('');

    try {
      // Combiner date et heure
      const dateTime = `${formData.appointmentDate}T${formData.appointmentTime}:00`;

      await appointmentsService.updateAppointment(appointment.id, {
        appointmentDate: dateTime,
        type: formData.type,
        status: formData.status,
        address: formData.address,
        notes: formData.notes || undefined,
      });

      onSuccess(); // Rafraîchir le dashboard
      onClose(); // Fermer le modal
    } catch (err: any) {
      setError(err.message || 'Erreur lors de la modification du rendez-vous');
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen || !appointment) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto">
      <div className="flex min-h-screen items-center justify-center p-4">
        {/* Overlay */}
        <div
          className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
          onClick={onClose}
        />

        {/* Modal */}
        <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 z-10">
          {/* Header */}
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-display font-bold text-gray-900 flex items-center">
              <Calendar className="h-6 w-6 mr-3 text-indigo-600" />
              Modifier le Rendez-vous
            </h2>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <X className="h-6 w-6 text-gray-500" />
            </button>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Client */}
            <div>
              <label htmlFor="clientId" className="block text-sm font-medium text-gray-700 mb-2">
                <User className="h-4 w-4 inline mr-2" />
                Client *
              </label>
              <select
                id="clientId"
                value={formData.clientId}
                onChange={(e) => setFormData({ ...formData, clientId: e.target.value })}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-100 cursor-not-allowed"
                disabled
              >
                <option value="">Sélectionner un client</option>
                {clients.map((client) => (
                  <option key={client.id} value={client.id}>
                    {client.fullName}
                  </option>
                ))}
              </select>
              <p className="text-xs text-gray-500 mt-1">Le client ne peut pas être modifié</p>
            </div>

            {/* Date et Heure */}
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label htmlFor="appointmentDate" className="block text-sm font-medium text-gray-700 mb-2">
                  Date *
                </label>
                <input
                  type="date"
                  id="appointmentDate"
                  value={formData.appointmentDate}
                  onChange={(e) => setFormData({ ...formData, appointmentDate: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                />
              </div>

              <div>
                <label htmlFor="appointmentTime" className="block text-sm font-medium text-gray-700 mb-2">
                  Heure *
                </label>
                <input
                  type="time"
                  id="appointmentTime"
                  value={formData.appointmentTime}
                  onChange={(e) => setFormData({ ...formData, appointmentTime: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                />
              </div>
            </div>

            {/* Type et Status */}
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-2">
                  Type *
                </label>
                <select
                  id="type"
                  value={formData.type}
                  onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                >
                  <option value="Installation">Installation</option>
                  <option value="Visite technique">Visite technique</option>
                  <option value="Signature">Signature</option>
                  <option value="SAV">SAV</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>

              <div>
                <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-2">
                  Statut *
                </label>
                <select
                  id="status"
                  value={formData.status}
                  onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                >
                  <option value="scheduled">Programmé</option>
                  <option value="confirmed">Confirmé</option>
                  <option value="urgent">Urgent</option>
                  <option value="completed">Terminé</option>
                  <option value="cancelled">Annulé</option>
                </select>
              </div>
            </div>

            {/* Adresse */}
            <div>
              <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-2">
                <MapPin className="h-4 w-4 inline mr-2" />
                Adresse *
              </label>
              <input
                type="text"
                id="address"
                value={formData.address}
                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="15 rue Victor Hugo, Lyon"
                required
              />
            </div>

            {/* Notes */}
            <div>
              <label htmlFor="notes" className="block text-sm font-medium text-gray-700 mb-2">
                <FileText className="h-4 w-4 inline mr-2" />
                Notes (optionnel)
              </label>
              <textarea
                id="notes"
                value={formData.notes}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                rows={3}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Informations supplémentaires..."
              />
            </div>

            {/* Error */}
            {error && (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {error}
              </div>
            )}

            {/* Actions */}
            <div className="flex items-center justify-end space-x-4 pt-4">
              <button
                type="button"
                onClick={onClose}
                className="px-6 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium"
              >
                Annuler
              </button>
              <button
                type="submit"
                disabled={loading}
                className="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-lg"
              >
                {loading ? 'Modification...' : 'Modifier le rendez-vous'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
