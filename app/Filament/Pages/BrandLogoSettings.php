<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Services\BrandLogoService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class BrandLogoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Brand & Logo';
    protected static ?string $navigationGroup = 'System & Support';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'brand-logo-settings';

    protected static string $view = 'filament.pages.brand-logo-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function getHeading(): string
    {
        return 'Brand & Logo Settings';
    }

    public function getSubheading(): string
    {
        return 'Upload your brand logo once and apply it dynamically across the Website, Admin Panel, User Portal, Login/Register, and Printable FBR Invoices.';
    }

    public function mount(): void
    {
        $this->form->fill([
            'make_transparent' => BrandLogoService::isTransparentEnabled(),
            'auto_generate_dark' => BrandLogoService::isAutoDarkEnabled(),
            'light_logo' => null,
            'dark_logo' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload Brand Logo')
                    ->description('Supports transparent PNG, SVG, WEBP, or white-background JPG/PNG logos. Auto-transparency and Dark Mode generation work automatically.')
                    ->schema([
                        Forms\Components\FileUpload::make('light_logo')
                            ->label('Primary Logo (Website Header, User Portal, Invoices, Light Mode)')
                            ->image()
                            ->disk('local')
                            ->directory('tmp-brand-uploads')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                            ->maxSize(4096)
                            ->helperText('Recommended: high-resolution horizontal PNG or JPG (e.g. 800–1200px wide). Transparent PNGs are preserved cleanly.')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('make_transparent')
                            ->label('Auto-Remove White Background (Make Transparent)')
                            ->helperText('ON = Automatically removes solid white background with smooth edges & tight cropping. OFF = Keeps your logo with its original background.')
                            ->default(true),

                        Forms\Components\Toggle::make('auto_generate_dark')
                            ->label('Auto-Generate Dark Mode Logo')
                            ->helperText('ON = Automatically converts dark text into crisp white for Website Footer & Admin Dark Mode. OFF = Upload your own Dark Logo below.')
                            ->default(true)
                            ->live(),

                        Forms\Components\FileUpload::make('dark_logo')
                            ->label('Custom Dark Mode Logo (Optional — Website Footer & Admin Dark Mode)')
                            ->image()
                            ->disk('local')
                            ->directory('tmp-brand-uploads')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                            ->maxSize(4096)
                            ->helperText('Optional. Leave empty to auto-generate a crisp white-text dark variant from your Primary Logo.')
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_default')
                ->label('Reset to Default Logo')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Restore Default InvoiceGO Logo')
                ->modalDescription('This will remove any custom uploaded logo and restore the built-in transparent InvoiceGO Light & Dark logos everywhere.')
                ->action(function () {
                    app(BrandLogoService::class)->resetToDefault();

                    ActivityLog::record(
                        'brand_logo_reset',
                        'Super Admin reset the platform brand logo back to the default InvoiceGO logo.'
                    );

                    Notification::make()
                        ->title('Default Logo Restored')
                        ->body('The original transparent InvoiceGO Light & Dark logos are now active everywhere.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $lightStored = is_array($state['light_logo'] ?? null)
            ? (array_values($state['light_logo'])[0] ?? null)
            : ($state['light_logo'] ?? null);

        $darkStored = is_array($state['dark_logo'] ?? null)
            ? (array_values($state['dark_logo'])[0] ?? null)
            : ($state['dark_logo'] ?? null);

        $lightPath = BrandLogoService::resolveDiskPath($lightStored);
        $darkPath = BrandLogoService::resolveDiskPath($darkStored);

        if (!$lightPath && !$darkPath) {
            Notification::make()
                ->title('No Logo Selected')
                ->body('Please upload a Primary Logo or Custom Dark Mode Logo before saving.')
                ->warning()
                ->send();
            return;
        }

        $makeTransparent = (bool) ($state['make_transparent'] ?? true);
        $autoGenerateDark = (bool) ($state['auto_generate_dark'] ?? true);

        app(BrandLogoService::class)->saveUploadedLogos(
            $lightPath,
            $darkPath,
            $makeTransparent,
            $autoGenerateDark
        );

        // Clean up temporary upload files
        foreach ([$lightStored, $darkStored] as $tmp) {
            if (!empty($tmp) && Storage::disk('local')->exists($tmp)) {
                Storage::disk('local')->delete($tmp);
            }
        }

        ActivityLog::record(
            'brand_logo_updated',
            'Super Admin uploaded and applied a new dynamic brand logo across the platform.',
            [
                'transparent' => $makeTransparent,
                'auto_dark' => $autoGenerateDark,
                'custom_dark_uploaded' => !empty($darkPath),
            ]
        );

        // Reset upload inputs while preserving toggles
        $this->form->fill([
            'make_transparent' => $makeTransparent,
            'auto_generate_dark' => $autoGenerateDark,
            'light_logo' => null,
            'dark_logo' => null,
        ]);

        Notification::make()
            ->title('Brand Logo Updated Everywhere')
            ->body('Your new logo is now live across the Website, Admin Panel, User Portal, Login/Register, and Printable Invoices.')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        return [
            'lightLogoUrl' => BrandLogoService::lightUrl(),
            'darkLogoUrl' => BrandLogoService::darkUrl(),
            'isCustom' => BrandLogoService::hasCustomLogo(),
            'isTransparent' => BrandLogoService::isTransparentEnabled(),
            'isAutoDark' => BrandLogoService::isAutoDarkEnabled(),
        ];
    }
}
