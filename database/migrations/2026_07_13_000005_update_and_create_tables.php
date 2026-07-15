<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create cow_groups first since savings_plans references it
        Schema::create('cow_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('hijri_year');
            $table->integer('filled_slots')->default(0);
            $table->string('status')->default('open'); // open, full, ready, processed
            $table->timestamps();
        });

        // 2. Rename livestock to animal_packages
        if (Schema::hasTable('livestock')) {
            Schema::rename('livestock', 'animal_packages');
        }

        // 3. Update animal_packages columns
        Schema::table('animal_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('animal_packages', 'type')) {
                $table->string('type')->default('domba'); // domba, kambing, sapi_utuh, sapi_patungan
            }
            if (!Schema::hasColumn('animal_packages', 'total_slots')) {
                $table->integer('total_slots')->default(1);
            }
            if (!Schema::hasColumn('animal_packages', 'bundle_quantity')) {
                $table->integer('bundle_quantity')->default(1);
            }
            if (!Schema::hasColumn('animal_packages', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('animal_packages', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 4. Rename locations to distribution_locations
        if (Schema::hasTable('locations')) {
            Schema::rename('locations', 'distribution_locations');
        }

        // 5. Update distribution_locations columns
        Schema::table('distribution_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('distribution_locations', 'code')) {
                $table->string('code')->nullable()->unique();
            }
            if (!Schema::hasColumn('distribution_locations', 'category')) {
                $table->string('category')->default('qurban'); // qurban, aqiqah
            }
            if (!Schema::hasColumn('distribution_locations', 'quota')) {
                $table->integer('quota')->default(100);
            }
            if (!Schema::hasColumn('distribution_locations', 'used_quota')) {
                $table->integer('used_quota')->default(0);
            }
            $table->integer('capacity')->nullable()->change();
        });

        // 6. Rename savings to savings_plans
        if (Schema::hasTable('savings')) {
            Schema::rename('savings', 'savings_plans');
        }

        // 7. Update savings_plans columns
        Schema::table('savings_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('savings_plans', 'plan_code')) {
                $table->string('plan_code')->nullable()->unique();
            }
            if (!Schema::hasColumn('savings_plans', 'locked_price')) {
                $table->decimal('locked_price', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'collected_amount')) {
                if (Schema::hasColumn('savings_plans', 'current_amount')) {
                    $table->renameColumn('current_amount', 'collected_amount');
                } else {
                    $table->decimal('collected_amount', 15, 2)->default(0.0);
                }
            }
            if (!Schema::hasColumn('savings_plans', 'hijri_year')) {
                $table->integer('hijri_year')->default(1447);
            }
            if (!Schema::hasColumn('savings_plans', 'is_institutional')) {
                $table->boolean('is_institutional')->default(false);
            }
            if (!Schema::hasColumn('savings_plans', 'shohibul_name')) {
                $table->string('shohibul_name')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'aqiqah_child_name')) {
                $table->string('aqiqah_child_name')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'aqiqah_child_gender')) {
                $table->string('aqiqah_child_gender')->nullable(); // putra, putri
            }
            if (!Schema::hasColumn('savings_plans', 'aqiqah_child_birthdate')) {
                $table->date('aqiqah_child_birthdate')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'requested_execution_date')) {
                $table->date('requested_execution_date')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'scheduled_execution_date')) {
                $table->date('scheduled_execution_date')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'repriced_at')) {
                $table->timestamp('repriced_at')->nullable();
            }
            if (!Schema::hasColumn('savings_plans', 'cow_group_id')) {
                $table->foreignId('cow_group_id')->nullable()->constrained('cow_groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('savings_plans', 'distribution_location_id')) {
                $table->foreignId('distribution_location_id')->nullable()->constrained('distribution_locations')->nullOnDelete();
            }
            if (!Schema::hasColumn('savings_plans', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 8. Update transactions columns to point to savings_plans
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'savings_id')) {
                $table->renameColumn('savings_id', 'savings_plan_id');
            }
            if (!Schema::hasColumn('transactions', 'channel')) {
                $table->string('channel')->nullable(); // Maps from payment_method
            }
            if (!Schema::hasColumn('transactions', 'is_manual')) {
                $table->boolean('is_manual')->default(false);
            }
            if (!Schema::hasColumn('transactions', 'manual_note')) {
                $table->text('manual_note')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'snap_token')) {
                if (Schema::hasColumn('transactions', 'token')) {
                    $table->renameColumn('token', 'snap_token');
                } else {
                    $table->string('snap_token')->nullable();
                }
            }
        });

        // 9. Create payment_notifications table
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->json('raw_payload');
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });

        // 10. Create group_location_votes table
        Schema::create('group_location_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cow_group_id')->constrained('cow_groups')->onDelete('cascade');
            $table->foreignId('savings_plan_id')->constrained('savings_plans')->onDelete('cascade');
            $table->foreignId('distribution_location_id')->constrained('distribution_locations')->onDelete('cascade');
            $table->timestamp('voted_at');
            $table->timestamps();
        });

        // 11. Create distribution_progress table
        Schema::create('distribution_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->nullable()->constrained('savings_plans')->onDelete('cascade');
            $table->foreignId('cow_group_id')->nullable()->constrained('cow_groups')->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->string('evidence')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });

        // 12. Create certificates table
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained('savings_plans')->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('pdf_path')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        // 13. Create refunds table
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained('savings_plans')->onDelete('cascade');
            $table->decimal('amount_collected', 15, 2);
            $table->decimal('fee_percent', 5, 2);
            $table->decimal('fee_amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->string('bank_account');
            $table->string('proof_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });

        // 14. Create price_histories table
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->string('animal_package_id');
            $table->decimal('price', 15, 2);
            $table->integer('hijri_year');
            $table->timestamps();
        });

        // 15. Create settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 16. Create admins table
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('distribution_progress');
        Schema::dropIfExists('group_location_votes');
        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('cow_groups');

        // Rollback transactions table changes (re-adding savings_id/token for rollback)
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'savings_plan_id')) {
                $table->renameColumn('savings_plan_id', 'savings_id');
            }
            if (Schema::hasColumn('transactions', 'snap_token')) {
                $table->renameColumn('snap_token', 'token');
            }
        });

        // Rollback savings_plans rename and columns
        if (Schema::hasTable('savings_plans')) {
            Schema::table('savings_plans', function (Blueprint $table) {
                if (Schema::hasColumn('savings_plans', 'collected_amount')) {
                    $table->renameColumn('collected_amount', 'current_amount');
                }
            });
            Schema::rename('savings_plans', 'savings');
        }

        // Rollback distribution_locations rename
        if (Schema::hasTable('distribution_locations')) {
            Schema::rename('distribution_locations', 'locations');
        }

        // Rollback animal_packages rename
        if (Schema::hasTable('animal_packages')) {
            Schema::rename('animal_packages', 'livestock');
        }
    }
};
