import { ref } from 'vue'

interface ConfirmationDialogOptions {
  title?: string
  message?: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}

interface ConfirmationDialogState {
  isOpen: boolean
  title: string
  message: string
  confirmText: string
  cancelText: string
  variant: 'default' | 'destructive'
  onConfirm?: () => void
  onCancel?: () => void
}

const defaultOptions: ConfirmationDialogOptions = {
  title: 'Confirmar acción',
  message: '¿Estás seguro de que quieres realizar esta acción?',
  confirmText: 'Confirmar',
  cancelText: 'Cancelar',
  variant: 'default'
}

export function useConfirmationDialog() {
  const dialogState = ref<ConfirmationDialogState>({
    isOpen: false,
    title: defaultOptions.title!,
    message: defaultOptions.message!,
    confirmText: defaultOptions.confirmText!,
    cancelText: defaultOptions.cancelText!,
    variant: defaultOptions.variant!
  })

  const openDialog = (options: ConfirmationDialogOptions = {}) => {
    const mergedOptions = { ...defaultOptions, ...options }
    
    return new Promise<boolean>((resolve) => {
      dialogState.value = {
        isOpen: true,
        title: mergedOptions.title!,
        message: mergedOptions.message!,
        confirmText: mergedOptions.confirmText!,
        cancelText: mergedOptions.cancelText!,
        variant: mergedOptions.variant!,
        onConfirm: () => {
          dialogState.value.isOpen = false
          resolve(true)
        },
        onCancel: () => {
          dialogState.value.isOpen = false
          resolve(false)
        }
      }
    })
  }

  const closeDialog = () => {
    dialogState.value.isOpen = false
  }

  return {
    dialogState,
    openDialog,
    closeDialog
  }
} 